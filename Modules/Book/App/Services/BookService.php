<?php
// Modules/Book/Appいつも/Services/BookService.php
namespace Modules\Book\App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Modules\Book\App\Contracts\BookServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BookService implements BookServiceInterface
{
    public function getAll(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Book::with(['addedBy', 'forums', 'threads', 'posts' ])
            ->withCount(['forums', 'threads', 'posts']);

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

    public function find(int $id): ?Book
    {
        return Book::where('id', $id)
            ->with(['addedBy', 'forums', 'threads', 'posts', 'challenges', 'userChallengeBooks'])
            ->firstOrFail();
    }

    public function create(array $data): Book
    {
        // Check for duplicate ISBN or title
        if (isset($data['isbn']) && Book::where('isbn', $data['isbn'])->exists()) {
            throw new \Exception('A book with this ISBN already exists.');
        }

        if (isset($data['title']) && Book::where('title', $data['title'])->where('author', $data['author'])->exists()) {
            throw new \Exception('A book with this title and author already exists.');
        }

        // Handle cover image upload
        if (isset($data['cover_image']) && !is_string($data['cover_image']) && $data['cover_image']->isValid()) {
            $path = $data['cover_image']->store('book-covers', 'public');
            $data['cover_image'] = $path;
        }

        // Set added_by to current user
        $data['added_by'] = Auth::id();

        return Book::create($data);
    }

    public function update(int $id, array $data): Book
    {
        $book = Book::where('id', $id)->firstOrFail();

        // Check for duplicate ISBN (excluding current book)
        if (isset($data['isbn']) && Book::where('isbn', $data['isbn'])->where('id', '!=', $id)->exists()) {
            throw new \Exception('A book with this ISBN already exists.');
        }

        // Handle cover image update
        if (isset($data['cover_image']) && !is_string($data['cover_image']) && $data['cover_image']->isValid()) {
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $path = $data['cover_image']->store('book-covers', 'public');
            $data['cover_image'] = $path;
        }

        $book->update($data);
        return $book;
    }

    public function delete(int $id): bool
    {
        $book = Book::where('id', $id)->firstOrFail();

        // Delete cover image if exists
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        return $book->delete();
    }

    public function searchGoogleBooks(string $query, int $page = 1, int $perPage = 20): array
    {
        $apiKey = config('Book.google_books_api_key');
        if (empty($apiKey)) {
            throw new \Exception('Google Books API key is missing.');
        }

        $startIndex = ($page - 1) * $perPage;
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => $query,
            'key' => $apiKey,
            'startIndex' => $startIndex,
            'maxResults' => $perPage,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch books from Google Books API.');
        }

        $data = $response->json();
        $books = [];

        foreach ($data['items'] ?? [] as $item) {
            $books[] = [
                'isbn' => $item['volumeInfo']['industryIdentifiers'][0]['identifier'] ?? null,
                'title' => $item['volumeInfo']['title'] ?? 'Unknown',
                'author' => $item['volumeInfo']['authors'][0] ?? 'Unknown',
                'cover_image' => $item['volumeInfo']['imageLinks']['thumbnail'] ?? null,
                'description' => $item['volumeInfo']['description'] ?? null,
                'publication_year' => $item['volumeInfo']['publishedDate'] ? substr($item['volumeInfo']['publishedDate'], 0, 4) : null,
                'genres' => $item['volumeInfo']['categories'] ?? [],
            ];
        }

        return [
            'items' => $books,
            'total' => $data['totalItems'] ?? 0,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function verify(int $id): Book
    {
        $book = Book::where('id', $id)->firstOrFail();
        $book->update(['verified' => true]);
        return $book;
    }
}
