<?php
namespace Modules\Forum\App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThreadApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'post_type' => $this->post_type,
            'is_pinned' => $this->is_pinned,
            'is_locked' => $this->is_locked,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'book' => $this->book ? [
                'id' => $this->book->id,
                'title' => $this->book->title,
            ] : null,
            'posts_count' => $this->posts_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
