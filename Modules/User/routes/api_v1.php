<?php

use Illuminate\Support\Facades\Route;
use Modules\User\App\Http\Controller\UserApiController;
use Modules\User\App\Http\Controller\ProfileApiController;

Route::middleware(['auth:sanctum'])->group(function () {
    // User management routes
Route::apiResource('user',UserApiController::class);
   Route::put('/user/{id}/ban', [UserApiController::class, 'ban']);
// Add these routes
Route::prefix('profile')->group(function () {
    Route::get('/', [ProfileApiController::class, 'show']);
    Route::put('/', [ProfileApiController::class, 'update']);
    Route::delete('/', [ProfileApiController::class, 'destroy']);

});
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{user}/followers', [UserApiController::class, 'followers'])->name('users.followers');
    Route::get('/users/{user}/following', [UserApiController::class, 'following'])->name('users.following');
    Route::post('/users/{user}/follow', [UserApiController::class, 'follow'])->name('users.follow');
    Route::delete('/users/{user}/follow', [UserApiController::class, 'unfollow'])->name('users.unfollow');
    Route::get('/users/{user}/stats', [UserApiController::class, 'stats'])->name('users.stats');
});

Route::middleware(['auth:sanctum' ])->group(function () {
    Route::delete('/admin/users/{user}/disable', [UserApiController::class, 'disable']);
    Route::put('/admin/users/{user}/role', [UserApiController::class, 'updateRole']);
});
