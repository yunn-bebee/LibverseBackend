<?php

namespace Modules\Event\App\Services;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventRsvp;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Event\App\Contracts\EventServiceInterface;

class EventService implements EventServiceInterface
{
public function getAll(array $filters = [], int $perPage = 10, int $page = 1): LengthAwarePaginator
{
    $query = Event::with(['creator', 'forum', 'rsvps']);

    if (Auth::user()->role === UserRole::ADMIN->label()) {
        $query = Event::with(['creator', 'forum', 'rsvps', 'rsvps.user', 'rsvps.user.profile']);
    }

 // Filter by user's RSVPs with optional status filter
    if (isset($filters['isrsvp']) && filter_var($filters['isrsvp'], FILTER_VALIDATE_BOOLEAN)) {
        $userId = Auth::id();
        if ($userId) {
            $query->whereHas('rsvps', function ($q) use ($userId, $filters) {
                $q->where('event_rsvps.user_id', $userId); // Explicit table name
                if (isset($filters['rsvp_status'])) {
                    $q->where('event_rsvps.status', '!=', $filters['rsvp_status']);
                }
            })->orWhereDoesntHave('rsvps', function ($q) use ($userId) {
                $q->where('event_rsvps.user_id', $userId);
            })->whereRaw('1 = 0'); // Force empty result for non-RSVPed events
        } else {
            throw new Exception('isrsvp filter applied without authenticated user', 423);

        }
    }
    // Filter by upcoming events
    if (isset($filters['upcoming']) && $filters['upcoming']) {
        $query->where('start_time', '>', now());
    }

    // Filter by past events
    if (isset($filters['past']) && $filters['past']) {
        $query->where('end_time', '<', now());
    }

    // Search by title
    if (isset($filters['search'])) {
        $query->where('title', 'like', '%' . $filters['search'] . '%');
    }

    // Order by start time (ascending for upcoming, descending for past)
    if (isset($filters['upcoming']) && $filters['upcoming']) {
        $query->orderBy('start_time', 'asc');
    } else {
        $query->orderBy('start_time', 'desc');
    }

    return $query->paginate($perPage, ['*'], 'page', $page);
}
    public function find(int $id): ?Event
    {
        return Event::with(['creator', 'forum', 'rsvps.user'])->find($id);
    }
public function create(array $data): Event
{
    return DB::transaction(function () use ($data) {
        $data['created_by'] = Auth::id();

        // Generate slug from title if not provided
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);

            // Ensure slug is unique
            $originalSlug = $data['slug'];
            $count = 1;
            while (Event::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }

        // Handle cover image upload
        if (isset($data['cover_image'])) {
            $path = $data['cover_image']->store('event-covers', 'public');
            $data['cover_image'] = $path;
        }

        $event = Event::create($data);

        return $event->load('creator', 'forum', 'rsvps');
    });
}
    public function update(int $id, array $data): Event
    {
        return DB::transaction(function () use ($id, $data) {
            $event = Event::findOrFail($id);

            // Handle cover image update
            if (isset($data['cover_image'])) {
                // Delete old cover image if exists
                if ($event->cover_image) {
                    Storage::disk('public')->delete($event->cover_image);
                }

                $path = $data['cover_image']->store('event-covers', 'public');
                $data['cover_image'] = $path;
            }

            $event->update($data);

            return $event->load('creator', 'forum', 'rsvps');
        });
    }

    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $event = Event::findOrFail($id);

            // Delete cover image if exists
            if ($event->cover_image) {
                Storage::disk('public')->delete($event->cover_image);
            }

            return $event->delete();
        });
    }

    public function rsvp(int $eventId, int $userId, string $status): bool
    {
        // Check if user already RSVP'd to this event
        $existingRsvp = EventRsvp::where('event_id', $eventId)
                                ->where('user_id', $userId)
                                ->first();

        if ($existingRsvp) {
            // Update existing RSVP
            $existingRsvp->update(['status' => $status]);
        } else {
            // Create new RSVP
            EventRsvp::create([
                'event_id' => $eventId,
                'user_id' => $userId,
                'status' => $status,
            ]);
        }

        return true;
    }

    public function getRsvpCounts(int $eventId): array
    {
        $counts = EventRsvp::where('event_id', $eventId)
                           ->select('status', DB::raw('count(*) as count'))
                           ->groupBy('status')
                           ->pluck('count', 'status')
                           ->toArray();

        return [
            'going' => $counts['going'] ?? 0,
            'interested' => $counts['interested'] ?? 0,
            'not_going' => $counts['not_going'] ?? 0,
        ];
    }
}
