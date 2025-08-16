<?php

namespace Modules\Forum\App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'content' => $this->content,
            'is_flagged' => $this->is_flagged,
            'user' => [
                'id' => $this->user?->uuid,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'book' => $this->whenLoaded('book', fn() => [
                'uuid' => $this->book?->id,
                'title' => $this->book?->title,
                'cover_image' => $this->book?->cover_image,
            ]),
            'parent_post_id' => $this->parent_post_id,
            'likes_count' => $this->whenCounted('likes'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
