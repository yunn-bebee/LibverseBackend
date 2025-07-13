<?php

namespace Modules\Book\App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Modules\Book\App\Contracts\BookServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookService implements BookServiceInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Book::with(['addedBy', 'forums', 'threads', 'posts', 'challenges', 'userChallengeBooks'])
            ->withCount(['forums', 'threads', 'posts', 'userChallengeBooks']);

        // Apply filters
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                  ->orWhere('author', 'like', '%'.$filters['search'].'%')
                  ->orWhere('isbn', 'like', '%'.$filters['search'].'%');
            });
        }

        if (isset($filters['author'])) {
            $query->where('author', 'like', '%'.$filters['author'].'%');
        }

        if (isset($filters['min_year'])) {
            $query->where('publication_year', '>=', $filters['min_year']);
        }

        return $query->paginate($perPage);
    }

    public function find(string $uuid): ?Book
    {
        return Book::where('uuid', $uuid)
            ->with(['addedBy', 'forums', 'threads', 'posts', 'challenges', 'userChallengeBooks'])
            ->firstOrFail();
    }

    public function create(array $data): Book
    {
        // Handle cover image upload
        if (isset($data['cover_image']) && $data['cover_image']->isValid()) {
            $path = $data['cover_image']->store('book-covers', 'public');
            $data['cover_image'] = $path;
        }

        // Generate UUID
        $data['uuid'] = Str::uuid();
        
        // Set added_by to current user
        $data['added_by'] = Auth::id();

        return Book::create($data);
    }

    public function update(string $uuid, array $data): Book
    {
        $book = Book::where('uuid', $uuid)->firstOrFail();

        // Handle cover image update
        if (isset($data['cover_image']) && $data['cover_image']->isValid()) {
            // Delete old image if exists
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            
            $path = $data['cover_image']->store('book-covers', 'public');
            $data['cover_image'] = $path;
        }

        $book->update($data);
        return $book;
    }

    public function delete(string $uuid): bool
    {
        $book = Book::where('uuid', $uuid)->firstOrFail();
        
        // Delete cover image if exists
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        
        return $book->delete();
    }
}