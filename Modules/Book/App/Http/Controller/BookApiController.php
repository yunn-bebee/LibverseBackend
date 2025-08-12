<?php

namespace Modules\Book\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use Modules\Book\App\Contracts\BookServiceInterface;
use Modules\Book\App\Http\Requests\BookRequest;
use Modules\Book\App\Http\Requests\BookSearchRequest;
use Modules\Book\App\Http\Requests\GoogleBookCreateRequest;
use Modules\Book\App\Resources\BookApiResource;
use App\Http\Controllers\Controller;

class BookApiController extends Controller
{
    public function __construct(
        protected BookServiceInterface $bookService
    ) {}

    public function index(BookSearchRequest $request): JsonResponse
    {
        $books = $this->bookService->getAll($request->validated());
        return apiResponse(
            true,
            'Books retrieved successfully',
            BookApiResource::collection($books)
        );
    }

    public function store(BookRequest $request): JsonResponse
    {
        try {
            $book = $this->bookService->create($request->validated());
            return apiResponse(
                true,
                'Book created successfully',
                new BookApiResource($book),
                201
            );
        } catch (\Exception $e) {
            return apiResponse(
                false,
                $e->getMessage(),
                null,
                400
            );
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $book = $this->bookService->find($id);
            return apiResponse(
                true,
                'Book retrieved successfully',
                new BookApiResource($book)
            );
        } catch (\Exception $e) {
            return apiResponse(
                false,
                'Book not found',
                null,
                404
            );
        }
    }

    public function update(BookRequest $request, int $id): JsonResponse
    {
        try {
            $book = $this->bookService->update($id, $request->validated());
            return apiResponse(
                true,
                'Book updated successfully',
                new BookApiResource($book)
            );
        } catch (\Exception $e) {
            return apiResponse(
                false,
                $e->getMessage(),
                null,
                400
            );
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->bookService->delete($id);
            return apiResponse(
                true,
                'Book deleted successfully'
            );
        } catch (\Exception $e) {
            return apiResponse(
                false,
                $e->getMessage(),
                null,
                400
            );
        }
    }

    public function searchGoogle(BookSearchRequest $request): JsonResponse
    {
        try {
            $results = $this->bookService->searchGoogleBooks(
                $request->input('query'),
                $request->input('page', 1),
                $request->input('per_page', 20)
            );
            return apiResponse(
                true,
                'Google Books search results',
                $results
            );
        } catch (\Exception $e) {
            return apiResponse(
                false,
                $e->getMessage(),
                null,
                400
            );
        }
    }

    public function createFromGoogle(GoogleBookCreateRequest $request): JsonResponse
    {
        try {
            $book = $this->bookService->createFromGoogleBooks(
                $request->input('google_books_id'),
                $request->only(['description', 'genres'])
            );
            return apiResponse(
                true,
                'Book created successfully from Google Books',
                new BookApiResource($book),
                201
            );
        } catch (\Exception $e) {
            return apiResponse(
                false,
                $e->getMessage(),
                null,
                400
            );
        }
    }
}
