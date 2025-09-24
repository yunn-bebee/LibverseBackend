<?php

namespace Modules\Challenge\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\User\App\Resources\UserApiResource;

class UserProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            
            'user' => new UserApiResource($this->user),
            'email' => $this->email,
            'progress' => [
                'books_completed' => $this->progress['books_completed'],
                'target_count' => $this->progress['target_count'],
                'percentage' => $this->progress['percentage'],
                'has_badge_awarded' => $this->progress['has_badge'],
            ],
            'books' => $this->books,
            'joined_at' => $this->pivot->created_at,
        ];
    }
}
