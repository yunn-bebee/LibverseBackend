<?php

namespace Modules\Forum\App\Services;

use App\Models\Forum;
use App\Models\Thread;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Forum\App\Contracts\ThreadServiceInterface;
use Illuminate\Support\Facades\Log;

class ThreadService implements ThreadServiceInterface
{
    /**
     * Create a new thread in a forum.
     */
    public function create(Forum $forum, array $data): Thread
    {
        $thread = new Thread($data);
        $thread->forum_id = $forum->id;
        $thread->user_id = Auth::id();
        $thread->save();

        Log::info('Thread created', [
            'thread_id' => $thread->id,
            'forum_id' => $forum->id,
            'user_id' => Auth::id(),
        ]);

        return $thread;
    }

    /**
     * Fetch threads for a forum (paginated).
     */
    public function getByForum(Forum $forum, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Thread::where('forum_id', $forum->id)
            ->with(['user', 'book'])
            ->withCount('posts');

        if (isset($filters['post_type'])) {
            $query->where('post_type', $filters['post_type']);
        }
        if (isset($filters['is_pinned'])) {
            $query->where('is_pinned', filter_var($filters['is_pinned'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Fetch a thread by ID with posts.
     */
    public function getById(int $id): ?Thread
    {
        return Thread::with(['user', 'book', 'posts'])->findOrFail($id);
    }

    /**
     * Update a thread.
     */
    public function update(Thread $thread, array $data): Thread
    {
        $user = Auth::user();
        if (!$user || ($thread->user_id !== $user->id && !$user->hasRole('moderator'))) {
            throw new \Exception('Unauthorized to update thread.');
        }

        $thread->update($data);

        Log::info('Thread updated', [
            'thread_id' => $thread->id,
            'user_id' => $user->id,
        ]);

        return $thread;
    }

    /**
     * Delete a thread.
     */
    public function delete(Thread $thread): bool
    {
        $user = Auth::user();
        if (!$user || ($thread->user_id !== $user->id && !$user->hasRole('moderator'))) {
            throw new \Exception('Unauthorized to delete thread.');
        }

        $result = $thread->delete();

        Log::info('Thread deleted', [
            'thread_id' => $thread->id,
            'user_id' => $user->id,
        ]);

        return $result;
    }

    /**
     * Toggle is_pinned for a thread.
     */
    public function togglePin(Thread $thread): Thread
    {
        $user = Auth::user();
        if (!$user || ($thread->user_id !== $user->id && !$user->hasRole('moderator'))) {
            throw new \Exception('Unauthorized to toggle pin status.');
        }

        $thread->is_pinned = !$thread->is_pinned;
        $thread->save();

        Log::info('Thread pin status toggled', [
            'thread_id' => $thread->id,
            'is_pinned' => $thread->is_pinned,
            'user_id' => $user->id,
        ]);

        return $thread;
    }

    /**
     * Toggle is_locked for a thread.
     */
    public function toggleLock(Thread $thread): Thread
    {
        $user = Auth::user();
        if (!$user || ($thread->user_id !== $user->id && !$user->hasRole('moderator'))) {
            throw new \Exception('Unauthorized to toggle lock status.');
        }

        $thread->is_locked = !$thread->is_locked;
        $thread->save();

        Log::info('Thread lock status toggled', [
            'thread_id' => $thread->id,
            'is_locked' => $thread->is_locked,
            'user_id' => $user->id,
        ]);

        return $thread;
    }
}
