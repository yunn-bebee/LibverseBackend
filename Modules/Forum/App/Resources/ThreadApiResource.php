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
                'name' => $this->user->username,
            ],
            'book' => $this->book ? [
                'id' => $this->book->id,
                'title' => $this->book->title,
            ] : null,
            'posts_count' => $this->posts_count,
            'posts' => $this->posts->map(function ($post) {
                return [
                    'id' => $post->id,
                    'content' => $post->content,
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->username,
                    ],
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
