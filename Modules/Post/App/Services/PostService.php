<?php

namespace Modules\Post\App\Services;


use App\Models\Post;
use App\Models\Thread;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

use Modules\Post\App\Contracts\PostServiceInterface;
use Illuminate\Support\Facades\Log;

class PostService implements PostServiceInterface
{
    /**
     * Create a new post in a thread.
     */
    public function create(Thread $thread, array $data): Post
    {
        if ($thread->is_locked) {
            throw new \Exception('Cannot create post in a locked thread.');
        }

        $post = new Post($data);
        $post->thread_id = $thread->id;
        $post->user_id = Auth::id();
        $post->save();

        Log::info('Post created', [
            'post_id' => $post->id,
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
        ]);

        return $post;
    }

    /**
     * Fetch posts for a thread (paginated).
     */
    public function getByThread(Thread $thread, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Post::where('thread_id', $thread->id)
            ->with(['user', 'book'])
            ->withCount('likes');

        if (isset($filters['is_flagged'])) {
            $query->where('is_flagged', filter_var($filters['is_flagged'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['search'])) {
            $query->where('content', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Fetch a post by ID.
     */
    public function find(string $id): ?Post
    {
        return Post::with(['user', 'book', 'likes'])->findOrFail($id);
    }

    /**
     * Update a post.
     */
    public function update(Post $post, array $data): Post
    {
        $user = Auth::user();
        if (!$user || ($post->user_id !== $user->id && !$user->hasRole('moderator'))) {
            throw new \Exception('Unauthorized to update post.');
        }

        $post->update($data);

        Log::info('Post updated', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        return $post;
    }

    /**
     * Delete a post.
     */
    public function delete(Post $post): bool
    {
        $user = Auth::user();
        if (!$user || ($post->user_id !== $user->id && !$user->hasRole('moderator'))) {
            throw new \Exception('Unauthorized to delete post.');
        }

        $result = $post->delete();

        Log::info('Post deleted', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        return $result;
    }
}
