<?php

use Illuminate\Support\Facades\Route;
use Modules\Post\App\Http\Controller\PostApiController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('threads.posts', PostApiController::class)
        ->parameters(['threads' => 'thread']);
    Route::post('posts/{post}/like', [PostApiController::class, 'like']);
    Route::post('posts/{post}/save', [PostApiController::class, 'save']);
    Route::post('posts/{post}/comment', [PostApiController::class, 'comment']);
    Route::post('posts/{post}/flag', [PostApiController::class, 'flag']);
    Route::post('posts/{post}/media', [PostApiController::class, 'uploadMedia']);
});
