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

        return [  // No semicolon here—array declaration continues
            'id' => $this->id,
            'thread_id' => $this->thread_id,
            'uuid' => $this->uuid ?? $this->id,  // Use actual UUID if available
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
                        'file_url' => $media->file_url ? url($media->file_url) : null,
                        'file_type' => $media->file_type,
                      'thumbnail_url' => $media->thumbnail_url ? url($media->thumbnail_url) : null,
                        'caption' => $media->caption,
                    ];
                });
            }),
            'likes_count' => $this->likes_count ?? 0,
            'saves_count' => $this->saves_count ?? 0,
            'replies_count' => $this->replies_count ?? 0,
            'is_liked' => $user ? $this->likes->contains('id', $user->id) : false,
            'is_saved' => $user ? $this->saves->contains('id', $user->id) : false,
            'created_at' => $this->created_at,  // Null-safe operator (PHP 8+)
            'updated_at' => $this->updated_at,  // Null-safe operator (PHP 8+)
            'reports' => $this->whenLoaded('reports', function () {  // Moved inside the array
                return $this->reports->map(function ($report) {
                    return [
                        'id' => $report->id,  // Use report's own ID, not post_id
                        'user' => new UserApiResource($report->user),
                        'reason' => $report->reason,
                        'status' => $report->status,
                        'reviewed_at' => $report->reviewed_at,  // Null-safe
                        'reviewed_by' => $report->reviewer ? new UserApiResource($report->reviewer) : null,
                    ];
                });
            }),
        ];
    }
}
