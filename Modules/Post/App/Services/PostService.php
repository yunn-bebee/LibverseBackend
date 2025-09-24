<?php

namespace Modules\Post\App\Services;

use App\Models\Media;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Notification\App\Notifications\GenericNotification;
use Modules\Notification\App\Notifications\PostReportedNotification;
use Modules\Post\App\Contracts\PostServiceInterface;


class PostService implements PostServiceInterface
{
   public function create(Thread $thread, array $data, array $files = []): Post
    {
        if ($thread->is_locked) {
            throw new \Exception('Cannot create post in a locked thread.');
        }

        $post = new Post($data);
        $post->uuid = Str::uuid();
        $post->thread_id = $thread->id;
        $post->user_id = Auth::id();
        $post->parent_post_id = $data['parent_post_id'] ?? null;
        $post->save();

        // Handle media uploads if provided
        if (!empty($files)) {
            foreach ($files as $fileData) {
                $this->uploadMedia($post, $fileData);
            }
        }
           // Send notification
        $user = Auth::user();
        $user->notify(new GenericNotification(
            $user,
            'Post Created',
            "You've created a new post in thread '{$thread->title}'."
        ));

        Log::info('Post created', [
            'post_id' => $post->id,
            'thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'parent_post_id' => $post->parent_post_id,
        ]);

        return $post->load(['user', 'book', 'media', 'replies']);
    }
    public function getSavedPosts(int $perPage = 15): LengthAwarePaginator
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized to view saved posts.', 401);
        }

        return Post::whereHas('saves', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with([
                'user',
                'user.profile',
                'thread',
                'thread.forum',
                'media',
                'replies.user',
                'replies.user.profile',
                'replies.media',
            ])
            ->withCount(['likes', 'saves', 'replies'])
            ->paginate($perPage);
    }

    public function getByThread(Thread $thread, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Post::where('thread_id', $thread->id)
            ->whereNull('parent_post_id')
            ->with([
                'user',
                'user.profile',
                'book',
                'media',
                'replies.user',
                'replies.user.profile',
                'replies.media',
                'replies.replies.user',
                'replies.replies.user.profile',
                'replies.replies.media',
                'reports',
                'reports.user',
                'reports.user.profile',
                'reports.reviewer',
                'reports.reviewer.profile',
            ])
            ->withCount(['likes', 'saves', 'replies']);

        if (isset($filters['is_flagged'])) {
            $query->where('is_flagged', filter_var($filters['is_flagged'], FILTER_VALIDATE_BOOLEAN));
        }
        if (isset($filters['search'])) {
            $query->where('content', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function find(string $id): Post
    {
        return Post::with([
            'user',
            'user.profile',
            'book',
            'media',
            'replies.user',
            'replies.user.profile',
            'replies.media',
            'replies.replies.user',
            'replies.replies.user.profile',
            'replies.replies.media'
        ])
        ->withCount(['likes', 'saves', 'replies'])
        ->where('id', $id)
        ->firstOrFail();
    }

    public function update(Post $post, array $data): Post
    {
        $user = Auth::user();
        if (!$user || ($post->user_id !== $user->id && !$user->hasAnyRole(['moderator', 'admin']))) {
            throw new \Exception('Unauthorized to update post.', 403);
        }

        $post->update($data);
        $post->load(['user', 'book', 'media', 'replies']);
          // Send notification
        $user->notify(new GenericNotification(
            $user,
            'Post Updated',
            "You've updated your post in thread '{$post->thread->title}'.",

        ));
        Log::info('Post updated', [
            'post_id' => $post->id,
            'id' => $post->id,
            'user_id' => $user->id,
        ]);

        return $post;
    }

    public function delete(Post $post): bool
    {
        $user = Auth::user();
        if (!$user || ($post->user_id !== $user->id && !$user->hasAnyRole(['moderator', 'admin']))) {
            throw new \Exception('Unauthorized to delete post.', 403);
        }

        foreach ($post->media as $media) {
            if (Storage::disk('public')->exists($media->file_url)) {
                Storage::disk('public')->delete($media->file_url);
            }
            if ($media->thumbnail_url && Storage::disk('public')->exists($media->thumbnail_url)) {
                Storage::disk('public')->delete($media->thumbnail_url);
            }
            $media->delete();
        }

        $result = $post->delete();
         // Send notification
        $user->notify(new GenericNotification(
            $user,
            'Post Deleted',
            "You've deleted your post from thread '{$post->thread->title}'.",

        ));
        Log::info('Post deleted', [
            'post_id' => $post->id,
            'id' => $post->id,
            'user_id' => $user->id,
        ]);

        return $result;
    }

    public function toggleLike(Post $post, bool $like): bool
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized to like post.', 401);
        }

        if ($like) {
            $post->likes()->syncWithoutDetaching([$user->id]);
            Log::info('Post liked', ['post_id' => $post->id, 'id' => $post->id, 'user_id' => $user->id]);
        } else {
            $post->likes()->detach($user->id);
            Log::info('Post unliked', ['post_id' => $post->id, 'id' => $post->id, 'user_id' => $user->id]);
        }

        return true;
    }

    public function toggleSave(Post $post, bool $save): bool
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized to save post.', 401);
        }

        if ($save) {
            $post->saves()->syncWithoutDetaching([$user->id]);
            Log::info('Post saved', ['post_id' => $post->id, 'id' => $post->id, 'user_id' => $user->id]);
        } else {
            $post->saves()->detach($user->id);
            Log::info('Post unsaved', ['post_id' => $post->id, 'id' => $post->id, 'user_id' => $user->id]);
        }

        return true;
    }

    public function createComment(Post $post, array $data): Post
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized to comment.', 401);
        }

        $comment = new Post($data);
          $comment->uuid = Str::uuid();
        $comment->thread_id = $post->thread_id;
        $comment->user_id = $user->id;
        $comment->parent_post_id = $post->id;
        $comment->save();

        Log::info('Comment created', [
            'post_id' => $comment->id,
            'id' => $comment->id,
            'parent_post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        return $comment->load(['user', 'media']);
    }

    public function reportPost(Post $post, array $data): PostReport
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized to report post.', 401);
        }

        if ($post->reports()->where('user_id', $user->id)->where('status', 'pending')->exists()) {
            throw new \Exception('You have already reported this post.', 400);
        }

        $report = PostReport::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'reason' => $data['reason'] ?? null,
            'status' => 'pending',
        ]);

        $post->update([
            'is_flagged' => true,
            'flagged_at' => now(),
        ]);
          // Notify user of report submission
        $user->notify(new GenericNotification(
            $user,
            'Post Reported',
            "You've reported a post in thread '{$post->thread->title}'.",

        ));
        // Notify moderators and admins
        // $moderators = User::role(['moderator', 'admin'])->get();
        // Notification::send($moderators, new PostReportedNotification($post, $report));
       User::find(1)->notify( new GenericNotification(
            User::find(1),
            'Post Reported',
            "A post has been reported in thread '{$post->thread->title}'.",

        ));

        Log::info('Post reported', [
            'post_id' => $post->id,
            'id' => $post->id,
            'report_id' => $report->id,
            'user_id' => $user->id,
        ]);

        return $report;
    }

    public function getReportedPosts(int $perPage = 15): LengthAwarePaginator
    {
        return Post::whereHas('reports')
            ->with(['user', 'thread', 'thread.forum', 'reports', 'reports.user'])

            ->paginate($perPage);
    }
 public function toggleFlag(Post $post): bool
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('Unauthorized to flag post.');
        }

        $postOwner = User::find($post->user_id);

            $postOwner->notify(new GenericNotification(
                $postOwner,
                'Post Flagged for not following the guidelines',
                "Your post '{$post->title}' in thread '{$post->thread->title}' has been flagged for not following the community guidelines."
            ));


        $post->is_flagged = !$post->is_flagged;
        $post->save();

        Log::info('Post flag toggled', [
            'post_id' => $post->id,
            'is_flagged' => $post->is_flagged,
            'user_id' => $user->id,
        ]);

        return true;
    }

    public function unflag(Post $post): void
    {
        $user = Auth::user();


        $post->reports()->where('status', 'pending')->update([
            'status' => 'dismissed',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
        ]);

        $post->update([
            'is_flagged' => false,
            'flagged_at' => null,
        ]);
        $postOwner = User::find($post->user_id);

            $postOwner->notify(new GenericNotification(
                $postOwner,
                'Post Unflagged',
                "Your post '{$post->title}' in thread '{$post->thread->title}' has been unflagged."
            ));
        Log::info('Post unflagged', [
            'post_id' => $post->id,
            'id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function uploadMedia(Post $post, array $data): array
    {
        $user = Auth::user();
        if (!$user || $post->user_id !== $user->id) {
            throw new \Exception('Unauthorized to upload media.', 403);
        }

        $file = $data['file'];
        $allowedTypes = ['jpg', 'jpeg', 'png', 'mp4', 'pdf'];
        $extension = $file->getClientOriginalExtension();

        if (!in_array(strtolower($extension), $allowedTypes)) {
            throw new \Exception('Invalid file type. Allowed: JPG, PNG, MP4, PDF.', 400);
        }

        $path = $file->store('media', 'public');
        $fileUrl = Storage::url($path);

        $thumbnailUrl = null;
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $thumbnailUrl = $fileUrl; // Use the image itself as the thumbnail
        } elseif ($extension === 'mp4') {
            // Generate thumbnail using ApyHub API
            $thumbnailUrl = $this->generateVideoThumbnail($fileUrl);
        }

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
            'id' => $post->id,
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
 public function updateMedia(Media $media, array $data): array
    {
        $user = Auth::user();
        if (!$user || $media->user_id !== $user->id) {
            throw new \Exception('Unauthorized to update media.', 403);
        }

        $updates = [];
        if (isset($data['file'])) {
            $file = $data['file'];
            $allowedTypes = ['jpg', 'jpeg', 'png', 'mp4', 'pdf'];
            $extension = $file->getClientOriginalExtension();

            if (!in_array(strtolower($extension), $allowedTypes)) {
                throw new \Exception('Invalid file type. Allowed: JPG, PNG, MP4, PDF.', 400);
            }

            // Delete old files
            if (Storage::disk('public')->exists($media->file_url)) {
                Storage::disk('public')->delete($media->file_url);
            }
            if ($media->thumbnail_url && Storage::disk('public')->exists($media->thumbnail_url)) {
                Storage::disk('public')->delete($media->thumbnail_url);
            }

            $path = $file->store('media', 'public');
            $fileUrl = Storage::url($path);

            $thumbnailUrl = null;
            if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $thumbnailUrl = $fileUrl;
            } elseif ($extension === 'mp4') {
                $thumbnailUrl = $this->generateVideoThumbnail($fileUrl);
            }

            $updates['file_url'] = $fileUrl;
            $updates['file_type'] = $extension;
            $updates['thumbnail_url'] = $thumbnailUrl;
        }

        if (isset($data['caption'])) {
            $updates['caption'] = $data['caption'];
        }

        $media->update($updates);

        Log::info('Media updated', [
            'media_id' => $media->id,
            'post_id' => $media->post_id,
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
        public function deleteMedia(Media $media): bool
    {
        $user = Auth::user();
        if (!$user || ($media->user_id !== $user->id && !$user->hasAnyRole(['moderator', 'admin']))) {
            throw new \Exception('Unauthorized to delete media.', 403);
        }

        if (Storage::disk('public')->exists($media->file_url)) {
            Storage::disk('public')->delete($media->file_url);
        }
        if ($media->thumbnail_url && Storage::disk('public')->exists($media->thumbnail_url)) {
            Storage::disk('public')->delete($media->thumbnail_url);
        }

        $result = $media->delete();

        Log::info('Media deleted', [
            'media_id' => $media->id,
            'post_id' => $media->post_id,
            'user_id' => $user->id,
        ]);

        return $result;
    }
    protected function generateVideoThumbnail(string $fileUrl): ?string
    {
        try {
            $apiKey = config('post.apyhub.api_key');
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->post('https://api.apyhub.com/generate/video-thumbnail/file', [
                'input' => config('app.url') . $fileUrl, // Full URL to the video
                'output' => 'thumbnail.jpg',
            ]);

            if ($response->successful()) {
                $thumbnailUrl = $response->json()['data']['output'];
                // Download and store the thumbnail
                $thumbnailContent = Http::get($thumbnailUrl)->body();
                $thumbnailPath = 'media/thumbnails/' . Str::uuid() . '.jpg';
                Storage::disk('public')->put($thumbnailPath, $thumbnailContent);
                return Storage::url($thumbnailPath);
            }

            Log::error('Failed to generate video thumbnail', [
                'file_url' => $fileUrl,
                'response' => $response->json(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error generating video thumbnail', [
                'file_url' => $fileUrl,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
