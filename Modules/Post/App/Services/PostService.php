<?php

namespace Modules\Post\App\Services;

use App\Models\Post;
use App\Models\Thread;
use App\Models\Media;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Post\App\Contracts\PostServiceInterface;

class PostService implements PostServiceInterface
{
    public function create(Thread $thread, array $data): Post
    {
        if ($thread->is_locked) {
            throw new \Exception('Cannot create post in a locked thread.');
        }

        $post = new Post($data);
        $post->thread_id = $thread->id;
        $post->user_id = Auth::id();
        $post->parent_post_id = $data['parent_post_id'] ?? null;
        $post->save();

        Log::info('Post created', [
            'post_id' => $post->id,
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'parent_post_id' => $post->parent_post_id,
        ]);

        return $post;
    }

    public function getByThread(Thread $thread, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Post::where('thread_id', $thread->id)
            ->with(['user', 'book', 'likes', 'replies'])
            ->withCount('likes');

        if (isset($filters['is_flagged'])) {
            $query->where('is_flagged', filter_var($filters['is_flagged'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['search'])) {
            $query->where('content', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function find(string $id): ?Post
    {
        return Post::with(['user', 'book', 'likes', 'replies', 'media'])->findOrFail($id);
    }

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

    public function toggleLike(Post $post, bool $like): bool
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized to like post.');
        }

        if ($like) {
            $post->likes()->attach($user->id);
            Log::info('Post liked', ['post_id' => $post->id, 'user_id' => $user->id]);
        } else {
            $post->likes()->detach($user->id);
            Log::info('Post unliked', ['post_id' => $post->id, 'user_id' => $user->id]);
        }

        return true;
    }

    public function toggleSave(Post $post, bool $save): bool
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized to save post.');
        }

        if ($save) {
            $post->saves()->attach($user->id);
            Log::info('Post saved', ['post_id' => $post->id, 'user_id' => $user->id]);
        } else {
            $post->saves()->detach($user->id);
            Log::info('Post unsaved', ['post_id' => $post->id, 'user_id' => $user->id]);
        }

        return true;
    }

    public function createComment(Post $post, array $data): Post
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized to comment.');
        }

        $comment = new Post($data);
        $comment->thread_id = $post->thread_id;
        $comment->user_id = $user->id;
        $comment->parent_post_id = $post->id;
        $comment->save();

        Log::info('Comment created', [
            'post_id' => $comment->id,
            'parent_post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        return $comment;
    }

    public function toggleFlag(Post $post): bool
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized to flag post.');
        }

        $post->is_flagged = !$post->is_flagged;
        $post->save();

        Log::info('Post flag toggled', [
            'post_id' => $post->id,
            'is_flagged' => $post->is_flagged,
            'user_id' => $user->id,
        ]);

        return true;
    }

    public function uploadMedia(Post $post, array $data): array
    {
        $user = Auth::user();
        if (!$user || $post->user_id !== $user->id) {
            throw new \Exception('Unauthorized to upload media.');
        }

        $file = $data['file'];
        $allowedTypes = ['jpg', 'jpeg', 'png', 'mp4', 'pdf'];
        $extension = $file->getClientOriginalExtension();
        if (!in_array(strtolower($extension), $allowedTypes)) {
            throw new \Exception('Invalid file type. Allowed: JPG, PNG, MP4, PDF.');
        }

        $path = $file->store('media', 'public');
        $fileUrl = Storage::url($path);
        $thumbnailUrl = in_array($extension, ['jpg', 'jpeg', 'png']) ? $fileUrl : ($data['thumbnail_url'] ?? null);

        $media = Media::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'file_url' => $fileUrl,
            'file_type' => $extension,
            'thumbnail_url' => $thumbnailUrl,
            'caption' => $data['caption'] ?? null,
        ]);

        Log::info('Media uploaded', [
            'media_id' => $media->id,
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        return [
            'id' => $media->id,
            'file_url' => $media->file_url,
            'file_type' => $media->file_type,
            'thumbnail_url' => $media->thumbnail_url,
            'caption' => $media->caption,
        ];
    }
}
