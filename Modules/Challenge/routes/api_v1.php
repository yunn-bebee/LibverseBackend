
<?php

use Illuminate\Support\Facades\Route;
use Modules\Challenge\App\Http\Controller\ChallengeApiController;

// Public endpoints
Route::get('challenges/{challenge}/books', [ChallengeApiController::class, 'getBooks'])->name('challenges.books');

// Authenticated endpoints
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('challenges', [ChallengeApiController::class, 'index'])->name('challenges.index');
    Route::get('challenges/{challenge}', [ChallengeApiController::class, 'show'])->name('challenges.show');
    // User participation
    Route::post('challenges/{challenge}/join', [ChallengeApiController::class, 'joinChallenge'])->name('challenges.join');
    Route::get('challenges/{challenge}/progress', [ChallengeApiController::class, 'getUserProgress'])->name('challenges.progress');
    Route::put('challenges/books/{record}/status', [ChallengeApiController::class, 'updateBookStatus'])->name('challenges.books.status');
    Route::get('challenges/{challenge}/leaderboard', [ChallengeApiController::class, 'getLeaderboard'])->name('challenges.leaderboard');

 Route::post('challenges/{challenge}/users/{user}/books', [ChallengeApiController::class, 'addUserBook'])->name('challenges.users.books.add');
    Route::delete('challenges/{challenge}/users/{user}/books/{bookId}', [ChallengeApiController::class, 'removeUserBook'])->name('challenges.users.books.remove');


});

// Admin endpoints
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Challenge CRUD
    Route::post('challenges', [ChallengeApiController::class, 'store'])->name('challenges.store');
    Route::put('challenges/{challenge}', [ChallengeApiController::class, 'update'])->name('challenges.update');
    Route::delete('challenges/{challenge}', [ChallengeApiController::class, 'destroy'])->name('challenges.destroy');

    // Book Management
    Route::get('challenges/{challenge}/books', [ChallengeApiController::class, 'getBooks'])->name('challenges.books.index');
    Route::post('challenges/{challenge}/books', [ChallengeApiController::class, 'addBook'])->name('challenges.books.add');
    Route::delete('challenges/{challenge}/books/{bookId}', [ChallengeApiController::class, 'removeBook'])->name('challenges.books.remove');

    // User Book Management

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
