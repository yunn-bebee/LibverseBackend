<?php

namespace Modules\Event\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Event\App\Contracts\EventServiceInterface;
use Modules\Event\App\Http\Requests\EventRequest;
use Modules\Event\App\Http\Requests\EventRsvpRequest;
use Modules\Event\App\Resources\EventApiResource;
use App\Helpers\apiResponse;
use Illuminate\Support\Facades\Auth;

class EventApiController extends Controller
{
    public function __construct(
        protected EventServiceInterface $eventService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['upcoming', 'past', 'search']);
        $paginationParams = getPaginationParams($request);

        $events = $this->eventService->getAll(
            $filters,
            $paginationParams['perPage'],
            $paginationParams['page']
        );

        return apiResponse(
            true,
            'Events retrieved successfully',
            EventApiResource::collection($events),
            200,
            [],
            $events
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventRequest $request): JsonResponse
    {
        $event = $this->eventService->create($request->validated());

        return apiResponse(
            true,
            'Event created successfully',
            new EventApiResource($event),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $event = $this->eventService->find((int) $id);

        if (!$event) {
            return apiResponse(false, 'Event not found', null, 404);
        }

        return apiResponse(
            true,
            'Event retrieved successfully',
            new EventApiResource($event),
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventRequest $request, string $id): JsonResponse
    {
        $event = $this->eventService->update((int) $id, $request->validated());

        return apiResponse(
            true,
            'Event updated successfully',
            new EventApiResource($event),
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->eventService->delete((int) $id);

        if (!$deleted) {
            return apiResponse(false, 'Event not found', null, 404);
        }

        return apiResponse(
            true,
            'Event deleted successfully',
            null,
            204
        );
    }

    /**
     * Handle RSVP for an event.
     */public function rsvp(EventRsvpRequest $request, string $eventId): JsonResponse
{
    $userId = Auth::id();
    $validated = $request->validated();
    $this->eventService->rsvp((int) $eventId, $userId, $validated['status']);

    return apiResponse(
        true,
        'RSVP updated successfully',
        null,
        200
    );
}

    /**
     * Get RSVP counts for an event.
     */
    public function rsvpCounts(string $eventId): JsonResponse
    {
        $counts = $this->eventService->getRsvpCounts((int) $eventId);

        return apiResponse(
            true,
            'RSVP counts retrieved successfully',
            $counts,
            200
        );
    }
}
