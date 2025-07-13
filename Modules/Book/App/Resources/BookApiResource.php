<?php

namespace Modules\Book\App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BookApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'library_book_id' => $this->library_book_id,
            'isbn' => $this->isbn,
            'title' => $this->title,
            'author' => $this->author,
            'cover_image' => $this->cover_image,
            'description' => $this->description,
            'verified' => $this->verified,
            'added_by' => [
                'id' => $this->addedBy?->id,
                'name' => $this->addedBy?->name,
                'email' => $this->addedBy?->email,
            ],
            // 'forums_count' => $this->forums->count(),
            // 'threads_count' => $this->threads->count(),
            // 'posts_count' => $this->posts->count(),
            // 'challenges' => $this->challenges->pluck('title'), // or use a ChallengeResource if needed
            // 'user_challenge_books_count' => $this->userChallengeBooks->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
