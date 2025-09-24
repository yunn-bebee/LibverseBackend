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
                'id' => $this->creator->id,
                'name' => $this->creator->username,
            ],
            'book' => $this->book ? [
                'id' => $this->book->id,
                'title' => $this->book->title,
            ] : null,
            'threads_count' => $this->threads_count,
            'participants_count' => $this->members_count ?? 0,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_joined' => $request->user() ? $this->members->contains($request->user()->id) : false,
            'is_pending' => $request->user() ? $this->joinRequests->contains($request->user()->id) : false,
            
        ];
    }
}
