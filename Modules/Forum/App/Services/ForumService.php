<?php

namespace Modules\Forum\App\Services;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Forum\App\Contracts\ForumServiceInterface;
use Illuminate\Support\Facades\Log;

class ForumService implements ForumServiceInterface
{
    /**
     * Fetch all forums with filters (paginated).
     */
    public function getAll(array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = Forum::with(['creator', 'book'])->withCount('threads');

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['is_public'])) {
            $query->where('is_public', filter_var($filters['is_public'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Fetch a forum by ID with threads.
     */
    public function find(int $id): ?Forum
    {
        return Forum::with(['creator', 'book', 'threads'])->findOrFail($id);
    }

    /**
     * Create a new forum.
     */
    public function create(array $data): Forum
    {
        $data['created_by'] = Auth::id();
        $data['slug'] = \Str::slug($data['name']);

        // Ensure slug uniqueness
        $originalSlug = $data['slug'];
        $count = 1;
        while (Forum::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $count++;
        }

        return Forum::create($data);
    }

    /**
     * Update a forum.
     */
    public function update(int $id, array $data): Forum
    {
        $forum = Forum::findOrFail($id);

        if (isset($data['name'])) {
            $data['slug'] = \Str::slug($data['name']);
            // Ensure slug uniqueness
            $originalSlug = $data['slug'];
            $count = 1;
            while (Forum::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $count++;
            }
        }

        $forum->update($data);
        return $forum;
    }

    /**
     * Delete a forum.
     */
    public function delete(int $id): bool
    {
        $forum = Forum::findOrFail($id);
        return $forum->delete();
    }

    /**
     * Fetch threads for a forum (paginated).
     */
    public function getThreads(Forum $forum, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Thread::where('forum_id', $forum->id)
            ->with(['user', 'book'])
            ->withCount('posts');

        if (isset($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a thread in a forum.
     */
    public function createThread(Forum $forum, array $data): Thread
    {
        $thread = new Thread($data);
        $thread->forum_id = $forum->id;
        $thread->user_id = Auth::id();
        $thread->save();
        return $thread;
    }

    /**
     * Toggle is_public for a forum.
     */
    public function togglePublic(Forum $forum): Forum
    {
        $user = Auth::user();
        if (!$user || ($forum->created_by !== $user->id && !$user->hasRole('moderator'))) {
            throw new \Exception('Unauthorized to toggle public status.');
        }

        $forum->is_public = !$forum->is_public;
        $forum->save();

        Log::info('Forum public status toggled', [
            'forum_id' => $forum->id,
            'is_public' => $forum->is_public,
            'user_id' => $user->id,
        ]);

        return $forum;
    }
}
