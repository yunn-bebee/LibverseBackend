<?php

namespace Modules\Post\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Modules\User\App\Resources\UserApiResource;

class PostApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = Auth::user();

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'content' => $this->content,
            'is_flagged' => $this->is_flagged,
            'user' => new UserApiResource($this->whenLoaded('user')),
            'book' => $this->whenLoaded('book', function () {
                return [
                    'uuid' => $this->book->uuid,
                    'title' => $this->book->title,
                    'cover_image' => $this->book->cover_image,
                ];
            }),
            'parent_post_id' => $this->parent_post_id,
            'replies' => PostApiResource::collection($this->whenLoaded('replies')),
            'media' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($media) {
                    return [
                        'id' => $media->id,
                        'file_url' => $media->file_url,
                        'file_type' => $media->file_type,
                        'thumbnail_url' => $media->thumbnail_url,
                        'caption' => $media->caption,
                    ];
                });
            }),
            'likes_count' => $this->likes_count ?? 0,
            'saves_count' => $this->saves_count ?? 0,
            'replies_count' => $this->replies_count ?? 0,
            'is_liked' => $user ? $this->likes->contains('id', $user->id) : false,
            'is_saved' => $user ? $this->saves->contains('id', $user->id) : false,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
