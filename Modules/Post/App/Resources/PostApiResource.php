<?php

namespace Modules\Post\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'content' => $this->content,
            'is_flagged' => $this->is_flagged,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'book' => $this->whenLoaded('book', fn () => [
                'uuid' => $this->book?->uuid,
                'title' => $this->book?->title,
                'cover_image' => $this->book?->cover_image,
            ]),
            'parent_post_id' => $this->parent_post_id,
            'replies' => PostApiResource::collection($this->whenLoaded('replies')),
            'media' => $this->whenLoaded('media', fn () => $this->media->map(fn ($media) => [
                'id' => $media->id,
                'file_url' => $media->file_url,
                'file_type' => $media->file_type,
                'thumbnail_url' => $media->thumbnail_url,
                'caption' => $media->caption,
            ])),
            'likes_count' => $this->whenCounted('likes'),
            'is_liked' => $this->when(auth()->check(), fn () => $this->likes()->where('user_id', auth()->id())->exists()),
            'is_saved' => $this->when(auth()->check(), fn () => $this->saves()->where('user_id', auth()->id())->exists()),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
