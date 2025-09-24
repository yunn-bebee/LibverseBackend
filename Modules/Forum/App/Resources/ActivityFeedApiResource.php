<?php

namespace Modules\Forum\App\Resources;

use App\Models\Forum;
use App\Models\Post;
use App\Models\Thread;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Modules\Forum\App\Resources\ForumApiResource;
use Modules\Forum\App\Resources\ThreadApiResource;
use Modules\Post\App\Resources\PostApiResource;

class ActivityFeedApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Generate a unique activity identifier
        $activityIdentifier = md5($this->activity_type . $this->created_at . $this->user_id . $this->forum_id . $this->title);

        $data = [
            'id' => $this->activity_id,
            'activity_identifier' => $activityIdentifier,
            'activity_type' => $this->activity_type,
            'created_at' => $this->created_at->toDateTimeString(),
            'user_id' => $this->user_id,
            'forum_id' => $this->forum_id,
            'title' => $this->title,
            'resource' => null,
        ];

        // Log for debugging
        Log::info('ActivityFeedApiResource', [
            'activity_type' => $this->activity_type,
            'activity_id' => $this->activity_id,
            'created_at' => $this->created_at,
        ]);

        // Load the full resource based on activity type
        if ($this->activity_type === 'forum') {
            $forum = Forum::with(['creator', 'book'])->withCount(['threads', 'members'])->find($this->activity_id);
            $data['resource'] = $forum ? new ForumApiResource($forum) : null;
        } elseif ($this->activity_type === 'thread') {
            $thread = Thread::with(['user', 'book', 'forum'])->withCount('posts')->find($this->activity_id);
            $data['resource'] = $thread ? new ThreadApiResource($thread) : null;
        } elseif ($this->activity_type === 'post') {
            $post = Post::with(['user', 'user.profile', 'book', 'media', 'thread', 'thread.forum'])
                ->withCount(['likes', 'saves', 'replies'])
                ->find($this->activity_id);
            $data['resource'] = $post ? new PostApiResource($post) : null;
        }

        return $data;
    }
}
