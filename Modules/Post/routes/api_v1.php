<?php

namespace Modules\Post;

use Illuminate\Support\Facades\Route;
use Modules\Post\App\Http\Controller\PostApiController;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('threads/{thread}/posts')->group(function () {
        Route::get('/', [PostApiController::class, 'index'])->name('post.index');
        Route::post('/', [PostApiController::class, 'store'])->name('post.store');
    });

    Route::prefix('posts')->group(function () {
        Route::get('{post}', [PostApiController::class, 'show'])->name('post.show');
        Route::put('{post}', [PostApiController::class, 'update'])->name('post.update');
        Route::delete('{post}', [PostApiController::class, 'destroy'])->name('post.destroy');
        Route::post('{post}/like', [PostApiController::class, 'like'])->name('post.like');
        Route::post('{post}/save', [PostApiController::class, 'save'])->name('post.save');
        Route::post('{post}/comment', [PostApiController::class, 'comment'])->name('post.comment');
        Route::post('{post}/report', [PostApiController::class, 'report'])->name('post.report');
        Route::post('{post}/media', [PostApiController::class, 'uploadMedia'])->name('post.upload-media');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('admin/reported-posts', [PostApiController::class, 'reportedPosts'])->name('post.reported-posts');
        Route::post('admin/posts/{post}/unflag', [PostApiController::class, 'unflagPost'])->name('post.unflag-post');
    });
});
