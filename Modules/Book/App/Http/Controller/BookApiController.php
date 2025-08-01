<?php

namespace Modules\Book\App\Http\Controller;

use Illuminate\Http\JsonResponse;
use Modules\Book\App\Contracts\BookServiceInterface;
use Modules\Book\App\Http\Requests\BookRequest;
use Modules\Book\App\Http\Requests\BookSearchRequest;
use Modules\Book\App\Resources\BookApiResource;
use App\Http\Controllers\Controller;
class BookApiController extends Controller
{
    public function __construct(
        protected BookServiceInterface $bookService
    ) {}

    /**
     * Display a listing of books (with search/filter)
     */
    public function index(BookSearchRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $books = $this->bookService->getAll($filters);

        return apiResponse(
            true,
            'Books retrieved successfully',
            BookApiResource::collection($books),
            );
    }

    /**
     * Store a newly created book
     */
    public function store(BookRequest $request): JsonResponse
    {
        $book = $this->bookService->create($request->validated());
        return apiResponse(
            true,
            'Book created successfully',
            new BookApiResource($book),
            201
        );
    }

    /**
     * Display the specified book
     */
    public function show(string $uuid): JsonResponse
    {
        $book = $this->bookService->find($uuid);
        return apiResponse(
            true,
            'Book retrieved successfully',
            new BookApiResource($book)
        );
    }

    /**
     * Update the specified book
     */
    public function update(BookRequest $request, string $uuid): JsonResponse
    {
        $book = $this->bookService->update($uuid, $request->validated());
        return apiResponse(
            true,
            'Book updated successfully',
            new BookApiResource($book)
        );
    }

    /**
     * Remove the specified book
     */
    public function destroy(string $uuid): JsonResponse
    {
        $this->bookService->delete($uuid);
        return apiResponse(
            true,
            'Book deleted successfully'
        );
    }

    public function verify(string $uuid): JsonResponse
    {
        try {
            $book = $this->bookService->verify($uuid);
            return apiResponse(
                true,
                'Book verified successfully',
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
}
