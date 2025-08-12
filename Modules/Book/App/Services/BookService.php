<?php
namespace Modules\Book\App\Services;
use Illuminate\Support\Facades\Config;
use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Modules\Book\App\Contracts\BookServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BookService implements BookServiceInterface
{

    public function getAll(array $filters = []): Collection
    {
        $query = Book::with(['addedBy', 'forums', 'threads', 'posts'])
            ->withCount(['forums', 'threads', 'posts']);

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

        return $query->get();
    }

    public function find(int $id): ?Book
    {
        return Book::with(['addedBy', 'forums', 'threads', 'posts'])
            ->findOrFail($id);
    }

   public function create(array $data): Book
{
    $data['library_book_id'] = 'BCL-' . Str::random(10);
    // Existing validation checks...
    if (isset($data['isbn']) && Book::where('isbn', $data['isbn'])->exists()) {
        throw new \Exception('A book with this ISBN already exists.');
    }

    if (Book::where('title', $data['title'])->where('author', $data['author'])->exists()) {
        throw new \Exception('A book with this title and author already exists.');
    }

    // Handle cover image upload...
    if (isset($data['cover_image']) && !is_string($data['cover_image'])) {
        $path = $data['cover_image']->store('book-covers', 'public');
        $data['cover_image'] = $path;
    }

    // Properly format genres
    if (isset($data['genres'])) {
        $data['genres'] = $this->formatGenres($data['genres']);
    }

    $data['added_by'] = Auth::id();
    return Book::create($data);
}

protected function formatGenres($genres)
{
    if (is_string($genres)) {
        // Convert comma-separated string to array
        return array_map('trim', explode(',', $genres));
    }

    if (is_array($genres)) {
        // Ensure it's a simple array of strings
        return array_values(array_filter(array_map('trim', $genres)));
    }

    return [];
}

    public function update(int $id, array $data): Book
    {
        $book = Book::findOrFail($id);

        if (isset($data['isbn']) && Book::where('isbn', $data['isbn'])->where('id', '!=', $id)->exists()) {
            throw new \Exception('A book with this ISBN already exists.');
        }

        if (isset($data['cover_image']) && !is_string($data['cover_image'])) {
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
        $book = Book::findOrFail($id);
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        return $book->delete();
    }

 public function searchGoogleBooks(string $query, int $page = 1, int $perPage = 20): array
{
    $apiKey = Config::get('Book.google_books_api_key');

    if (empty($apiKey)) {
        throw new \Exception('Google Books API key is missing.');
    }

    $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
        'q' => $query,
        'key' => $apiKey,
        'startIndex' => ($page - 1) * $perPage,
        'maxResults' => $perPage,
    ]);

    if ($response->failed()) {
        throw new \Exception('Failed to fetch books from Google Books API: ' . $response->body());
    }

    $data = $response->json();
    $books = [];

    foreach ($data['items'] ?? [] as $item) {
        $volumeInfo = $item['volumeInfo'] ?? [];

        // Extract categories (genres) from volumeInfo
        $genres = [];
        if (isset($volumeInfo['categories'])) {
            $genres = is_array($volumeInfo['categories'])
                ? $volumeInfo['categories']
                : [$volumeInfo['categories']];
        }

        $books[] = [
            'google_books_id' => $item['id'] ?? null,
            'title' => $volumeInfo['title'] ?? 'Unknown',
            'author' => $volumeInfo['authors'][0] ?? 'Unknown',
            'cover_image' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'isbn' => $this->extractIsbn($volumeInfo['industryIdentifiers'] ?? []),
            'publication_year' => isset($volumeInfo['publishedDate'])
                ? substr($volumeInfo['publishedDate'], 0, 4)
                : null,
            'genres' => $genres, // Add genres to the response
        ];
    }

    return [
        'items' => $books,
        'total' => $data['totalItems'] ?? 0,
        'page' => $page,
        'per_page' => $perPage,
    ];
}

    public function createFromGoogleBooks(string $googleBooksId, array $additionalData = []): Book
    {
            $apiKey = Config::get(key: 'Book.google_books_api_key');

        $response = Http::get("https://www.googleapis.com/books/v1/volumes/{$googleBooksId}", [
            'key' => $apiKey,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to fetch book details from Google Books API.');
        }

        $item = $response->json();
        $volumeInfo = $item['volumeInfo'] ?? [];
        $isbn = $this->extractIsbn($volumeInfo['industryIdentifiers'] ?? []);

        if ($isbn && Book::where('isbn', $isbn)->exists()) {
            throw new \Exception('This book already exists in our database.');
        }

        $bookData = [
            'title' => $volumeInfo['title'] ?? 'Unknown',
            'author' => $volumeInfo['authors'][0] ?? 'Unknown',
            'description' => $volumeInfo['description'] ?? null,
            'publication_year' => $volumeInfo['publishedDate'] ? substr($volumeInfo['publishedDate'], 0, 4) : null,
            'isbn' => $isbn,
            'added_by' => Auth::id(),
        ];

        if (isset($volumeInfo['imageLinks']['thumbnail'])) {
            $bookData['cover_image'] = $this->storeCoverFromUrl($volumeInfo['imageLinks']['thumbnail']);
        }

        return $this->create(array_merge($bookData, $additionalData));
    }

    protected function extractIsbn(array $identifiers): ?string
    {
        foreach ($identifiers as $identifier) {
            if ($identifier['type'] === 'ISBN_13') return $identifier['identifier'];
            if ($identifier['type'] === 'ISBN_10') return $identifier['identifier'];
        }
        return null;
    }

    protected function storeCoverFromUrl(string $url): string
    {
        $contents = file_get_contents($url);
        $filename = 'book-covers/' . uniqid() . '.jpg';
        Storage::disk('public')->put($filename, $contents);
        return $filename;
    }
}
