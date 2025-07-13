<?php

namespace Modules\Forum\App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ForumApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'is_public' => $this->is_public,

            'created_by' => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
                'email' => $this->creator?->email,
            ],

            'book' => [
                'id' => $this->book?->id,
                'title' => $this->book?->title,
                'cover_image' => $this->book?->cover_image,
            ],

            'threads_count' => $this->threads->count(),
            'events_count' => $this->events->count(),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
