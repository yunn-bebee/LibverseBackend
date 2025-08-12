<?php
// Modules/Book/routes/api_v1.php
use Illuminate\Support\Facades\Route;
use Modules\Book\App\Http\Controller\BookApiController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('book', BookApiController::class);
    Route::get('book/search/google', [BookApiController::class, 'searchGoogle'])->name('books.search.google');
    Route::post('book/google', [BookApiController::class, 'createFromGoogle'])->name('books.create.google');
});
