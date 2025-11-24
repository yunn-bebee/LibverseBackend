<?php

namespace Modules\Challenge\App\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Modules\Book\App\Resources\BookApiResource;

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
            'start_date' => $this->start_date,
            'end_date' => $this->end_date ,
            'participants_count' => $this->whenLoaded('participants', function () {
                return $this->participants->count();
            }),
            'badge' => $this->whenLoaded('badge', function () {
                return [
                    'id' => $this->badge->id,
                    'name' => $this->badge->name,
                    'image_url' => $this->badge->image_url,
                ];
            }),
            'challenge_books' => $this->whenLoaded('books', function () {
                return $this->books->map(function ($book) {
                    return [
                        'id' => $book->id,
                        'title' => $book->title,
                        'author' => $book->author,
                        'cover_image' => $book->cover_image,
                    ];
                });
            }),
            'has_joined' => $this->has_joined, // Default to false
            'created_at' => $this->created_at,
              ];

        // Only show dates and progress if user has joined
        if ($this->has_joined) {
            $response['start_date'] = $this->start_date;
            $response['end_date'] = $this->end_date;
            $response['progress'] = $this->progress ;
        }

        return $response;
    }
}
