<?php

namespace Modules\Challenge\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ChallengeApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $response = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'target_count' => $this->target_count,
            'is_active' => $this->is_active,
            'badge' => $this->whenLoaded('badge', function () {
                return [
                    'id' => $this->badge->id,
                    'name' => $this->badge->name,
                    'image_url' => $this->badge->image_url,
                ];
            }),
            'suggested_books' => $this->whenLoaded('books', function () {
                return $this->books->map(function ($book) {
                    return [
                        'id' => $book->id,
                        'title' => $book->title,
                        'author' => $book->author,
                        'cover_image' => $book->cover_image,
                    ];
                });
            }),
            'has_joined' => false, // Default to false
            'created_at' => $this->created_at,
        ];

        // Only show dates and progress if user has joined
        if ($this->has_joined) {
            $response['start_date'] = $this->start_date;
            $response['end_date'] = $this->end_date;
            $response['progress'] = $this->progress ?? [
                'books_read' => 0,
                'target' => $this->target_count,
                'percentage' => 0
            ];
        }

        return $response;
    }
}
