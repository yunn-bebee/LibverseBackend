<?php
namespace Modules\Forum\App\Resources;

use Modules\Book\App\Resources\BookApiResource;
use Modules\Post\App\Resources\PostApiResource;
use Modules\User\App\Resources\UserApiResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'forum_id' => $this->forum->id,
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'post_type' => $this->post_type,
            'is_pinned' => $this->is_pinned,
            'is_locked' => $this->is_locked,
            'user' => new UserApiResource($this->whenLoaded('user')),
            'book' => $this->book ? new BookApiResource($this->book) : null,
            'posts_count' => $this->posts->count(),
            'posts' => $this->posts->map(fn($post) => new PostApiResource($post)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
