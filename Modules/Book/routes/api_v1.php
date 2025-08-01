<?php
// Modules/Book/routes/api_v1.php
use Illuminate\Support\Facades\Route;
use Modules\Book\App\Http\Controller\BookApiController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('book', BookApiController::class);
    Route::get('book/search', [BookApiController::class, 'search'])->name('book.search');
    Route::patch('book/{id}/verify', [BookApiController::class, 'verify'])->name('book.verify')->middleware('role:Admin');
});
