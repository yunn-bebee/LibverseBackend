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

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Challenge CRUD
    Route::post('challenges', [ChallengeApiController::class, 'store'])->name('challenges.store');
    Route::put('challenges/{challenge}', [ChallengeApiController::class, 'update'])->name('challenges.update');
    Route::delete('challenges/{challenge}', [ChallengeApiController::class, 'destroy'])->name('challenges.destroy');

    // Bulk Update
    Route::post('challenges/bulk-update', [ChallengeApiController::class, 'bulkUpdate'])->name('challenges.bulk-update');

    // User Progress & Management
    Route::get('challenges/{challenge}/users', [ChallengeApiController::class, 'getChallengeParticipants'])->name('challenges.participants');
    Route::delete('challenges/{challenge}/users/{user}', [ChallengeApiController::class, 'removeUserFromChallenge'])->name('challenges.users.remove');
    Route::post('challenges/{challenge}/users/{user}/reset', [ChallengeApiController::class, 'resetUserProgress'])->name('challenges.users.reset');

    // Manual Badge Management
    Route::post('users/{user}/badges', [ChallengeApiController::class, 'manuallyAwardBadge'])->name('users.badges.award');
    Route::delete('users/{user}/badges/{badge}', [ChallengeApiController::class, 'manuallyRevokeBadge'])->name('users.badges.revoke');

    // Analytics
    Route::get('challenges/stats', [ChallengeApiController::class, 'getChallengeStats'])->name('challenges.stats');
});
