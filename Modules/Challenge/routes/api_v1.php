<?php

use Illuminate\Support\Facades\Route;
use Modules\Challenge\App\Http\Controller\ChallengeApiController;

// Public endpoints
Route::get('challenges', [ChallengeApiController::class, 'index']);
Route::get('challenges/{challenge}', [ChallengeApiController::class, 'show']);

// Authenticated endpoints
Route::middleware(['auth:sanctum'])->group(function () {
    // User participation
    Route::post('challenges/{challenge}/join', [ChallengeApiController::class, 'joinChallenge']);
    Route::get('challenges/{challenge}/progress', [ChallengeApiController::class, 'getUserProgress']);
    Route::post('challenges/{challenge}/add-book', [ChallengeApiController::class, 'addBook']);
    Route::put('challenges/books/{record}', [ChallengeApiController::class, 'updateBookStatus']);
    Route::get('challenges/{challenge}/leaderboard', [ChallengeApiController::class, 'getLeaderboard']);

    // Admin management
    Route::post('challenges', [ChallengeApiController::class, 'store']);
    Route::put('challenges/{challenge}', [ChallengeApiController::class, 'update']);
    Route::delete('challenges/{challenge}', [ChallengeApiController::class, 'destroy']);
});
