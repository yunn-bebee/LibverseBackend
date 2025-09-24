<?php

namespace Modules\Forum\App\Services;
use App\Models\Forum;
use App\Models\Thread;
use App\Models\Post;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\Forum\App\Contracts\ForumServiceInterface;

class ForumService implements ForumServiceInterface
{
    /**
     * Fetch all forums with filters (paginated).
     */
public function getAll(array $filters = [], int $perPage = 20, int $page = 1): LengthAwarePaginator
{
     $user = Auth::user();
    $query = Forum::with(['creator', 'book'])->withCount('threads', 'members');

    if (isset($filters['category'])) {
        $query->where('category', $filters['category']);
    }
    if (isset($filters['is_public'])) {
        $query->where('is_public', filter_var($filters['is_public'], FILTER_VALIDATE_BOOLEAN));
    }
    if (isset($filters['search'])) {
        $query->where('name', 'like', '%' . $filters['search'] . '%');
    }
    if (isset($filters['is_joined']) && filter_var($filters['is_joined'], FILTER_VALIDATE_BOOLEAN)) {
        $userId = Auth::id();
        $query->whereHas('members', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }
    // New filter: Include forums where followed users are approved members
        if (isset($filters['followedUsersJoined']) && filter_var($filters['followedUsersJoined'], FILTER_VALIDATE_BOOLEAN)) {
            $followedUserIds = $user->following()->pluck('users.id');
            $query->whereHas('members', function ($q) use ($followedUserIds) {
                $q->whereIn('users.id', $followedUserIds)->where('forum_user.status', 'approved');
            });
        }

    return $query->paginate($perPage);
}

    /**
     * Fetch a forum by ID with threads.
     */
    public function find(int $id): ?Forum
    {
        return Forum::with(['creator', 'book', 'threads'])->withCount('threads')->findOrFail($id);
    }

    /**
     * Create a new forum.
     */
    public function create(array $data): Forum
    {
        $data['created_by'] = Auth::id();
        $data['slug'] = Str::slug($data['name']);

        // Ensure slug uniqueness
        $originalSlug = $data['slug'];
        $count = 1;
        while (Forum::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $count++;
        }

        $forum = Forum::create($data);

        // Send notification
        $user = Auth::user();
        send_notification(
            $user,
            'Forum Created',
            "You've created the forum '{$forum->name}'.",
            url("/forums/{$forum->id}"),
            'View Forum'
        );

        Log::info('Forum created', [
            'forum_id' => $forum->id,
            'user_id' => Auth::id(),
        ]);

        return $forum;
    }

    /**
     * Update a forum.
     */
    public function update(int $id, array $data): Forum
    {
        $forum = Forum::findOrFail($id);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
            // Ensure slug uniqueness
            $originalSlug = $data['slug'];
            $count = 1;
            while (Forum::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
                $data['slug'] = $originalSlug . '-' . $count++;
            }
        }

        $forum->update($data);

        // Send notification
        $user = Auth::user();
        send_notification(
            $user,
            'Forum Updated',
            "You've updated the forum '{$forum->name}'.",
            url("/forums/{$forum->id}"),
            'View Forum'
        );

        Log::info('Forum updated', [
            'forum_id' => $forum->id,
            'user_id' => Auth::id(),
        ]);

        return $forum;
    }

    /**
     * Delete a forum.
     */
    public function delete(int $id): bool
    {
        $forum = Forum::findOrFail($id);
        $result = $forum->delete();

        // Send notification
        $user = Auth::user();
        send_notification(
            $user,
            'Forum Deleted',
            "You've deleted the forum '{$forum->name}'.",
            url('/forums'),
            'View Forums'
        );

        Log::info('Forum deleted', [
            'forum_id' => $id,
            'user_id' => Auth::id(),
        ]);

        return $result;
    }

    /**
     * Join a forum.
     */
    public function joinForum(User $user, Forum $forum): void
    {
        if ($user->forums()->where('forum_id', $forum->id)->exists()) {
            throw new \Exception('User is already a member or has a pending request', 400);
        }

        $status = $forum->is_public ? 'approved' : 'pending';
        $pivotData = ['status' => $status];
        if ($status === 'approved') {
            $pivotData['approved_at'] = now();
        }

        $user->forums()->syncWithoutDetaching([$forum->id => $pivotData]);

        if ($status === 'pending') {
            // Notification::send($forum->creator, new ForumJoinRequestNotification($user, $forum));
        } else {
            // \otification::send($user, new ForumJoinApprovedNotification($forum));
        }

        Log::info('User joined forum', [
            'user_id' => $user->id,
            'forum_id' => $forum->id,
            'status' => $status,
        ]);
    }

    /**
     * Leave a forum.
     */
    public function leaveForum(User $user, Forum $forum): void
    {
        if (!$user->forums()->where('forum_id', $forum->id)->exists()) {
            throw new \Exception('User is not a member of this forum', 400);
        }

        $user->forums()->detach($forum->id);

        Log::info('User left forum', [
            'user_id' => $user->id,
            'forum_id' => $forum->id,
        ]);
    }

    /**
     * List forum members.
     */
    /**
     * List forum members and join requests with status.
     */
    public function getForumMembers(Forum $forum, int $perPage = 15): LengthAwarePaginator
    {
        return $forum->joinRequests()
            ->with(['profile'])
            ->withPivot('status', 'approved_at')
            ->paginate($perPage);
    }


    /**
     * Approve a join request for a private forum.
     */
    public function approveJoinRequest(User $user, Forum $forum): void
    {
        if (!$user->forums()->where('forum_id', $forum->id)->where('status', 'pending')->exists()) {
            throw new \Exception('No pending join request found', 400);
        }

        $user->forums()->updateExistingPivot($forum->id, [
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // \Notification::send($user, new ForumJoinApprovedNotification($forum));

        Log::info('Join request approved', [
            'user_id' => $user->id,
            'forum_id' => $forum->id,
        ]);
    }

    /**
     * Reject a join request for a private forum.
     */
    public function rejectJoinRequest(User $user, Forum $forum): void
    {
        if (!$user->forums()->where('forum_id', $forum->id)->where('status', 'pending')->exists()) {
            throw new \Exception('No pending join request found', 400);
        }

        $user->forums()->updateExistingPivot($forum->id, [
            'status' => 'rejected',
            'approved_at' => null,
        ]);

        // \Notification::send($user, new ForumJoinRejectedNotification($forum));

        Log::info('Join request rejected', [
            'user_id' => $user->id,
            'forum_id' => $forum->id,
        ]);
    }

    /**
     * Fetch activity feed for followed users.
     */
   /**
     * Fetch activity feed for followed users and joined forums.
     */
 // ForumService.php (updated method)
     public function getActivityFeed(array $filters = []): LengthAwarePaginator
    {
        $user = Auth::user();
        $perPage = $filters['per_page'] ?? 15;

        // Get followed user IDs
        $followedUserIds = $user->following()->pluck('users.id');

        // Get joined forum IDs (approved members)
        $joinedForumIds = $user->forums()->wherePivot('status', 'approved')->pluck('forums.id');

        // Forums where followed users are members
        $forumsQuery = Forum::query()->select(
            DB::raw("'forum' as activity_type"),
            DB::raw('forums.id as activity_id'),
            'forums.created_at',
            'forums.created_by as user_id',
            'forums.id as forum_id',
            'forums.name as title'
        )
        ->join('forum_user', 'forums.id', '=', 'forum_user.forum_id')
        ->where('forum_user.status', 'approved')
        ->whereIn('forum_user.user_id', $followedUserIds);

        // Threads by followed users in public forums or joined private forums
        $threadsQuery = Thread::query()->select(
            DB::raw("'thread' as activity_type"),
            DB::raw('threads.id as activity_id'),
            'threads.created_at',
            'threads.user_id',
            'threads.forum_id',
            'threads.title'
        )
        ->join('forums', 'threads.forum_id', '=', 'forums.id')
        ->whereIn('threads.user_id', $followedUserIds)
        ->where(function ($query) use ($joinedForumIds) {
            $query->where('forums.is_public', true)
                  ->orWhereIn('threads.forum_id', $joinedForumIds);
        });

        // Posts by followed users in public forums or joined private forums
        $postsQuery = Post::query()->select(
            DB::raw("'post' as activity_type"),
            DB::raw('posts.id as activity_id'),
            'posts.created_at',
            'posts.user_id',
            'threads.forum_id',
            'posts.content as title'
        )
        ->join('threads', 'posts.thread_id', '=', 'threads.id')
        ->join('forums', 'threads.forum_id', '=', 'forums.id')
        ->whereIn('posts.user_id', $followedUserIds)
        ->whereNotNull('posts.thread_id')
        ->where(function ($query) use ($joinedForumIds) {
            $query->where('forums.is_public', true)
                  ->orWhereIn('threads.forum_id', $joinedForumIds);
        });

        // Combine queries
        $query = $forumsQuery->union($threadsQuery)->union($postsQuery);

        // Apply filters and pagination
        $query->orderBy('created_at', 'desc');
        return $query->paginate($perPage);
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

        // Send notification
        $user = Auth::user();
        send_notification(
            $user,
            'Thread Created',
            "You've created the thread '{$thread->title}' in '{$forum->name}'.",
            url("/threads/{$thread->id}"),
            'View Thread'
        );

        Log::info('Thread created', [
            'thread_id' => $thread->id,
            'forum_id' => $forum->id,
            'user_id' => Auth::id(),
        ]);

        return $thread;
    }

    /**
     * Toggle is_public for a forum.
     */
    public function togglePublic(Forum $forum): Forum
    {
        $user = Auth::user();
        $forum->is_public = !$forum->is_public;
        $forum->save();

        // Send notification
        send_notification(
            $user,
            'Forum Visibility Changed',
            "The forum '{$forum->name}' is now " . ($forum->is_public ? 'public' : 'private') . ".",
            url("/forums/{$forum->id}"),
            'View Forum'
        );

        Log::info('Forum public status toggled', [
            'forum_id' => $forum->id,
            'is_public' => $forum->is_public,
            'user_id' => $user->id,
        ]);

        return $forum;
    }
}

