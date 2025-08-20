<?php

namespace Modules\Forum;

use Illuminate\Support\Facades\Route;
use Modules\Forum\App\Http\Controller\ForumApiController;

    // Forum resource routes (index, store, show, update, destroy)
    Route::apiResource('forums', ForumApiController::class)->names([
        'index' => 'forum.index',
        'store' => 'forum.store',
        'show' => 'forum.show',
        'update' => 'forum.update',
        'destroy' => 'forum.destroy',
    ]);

    // Nested thread resource routes under forums
    Route::prefix('forums/{forum}')->group(function () {
        // List threads in a forum (GET /api/v1/forums/{forum}/threads)
        Route::get('threads', [ForumApiController::class, 'indexThreads'])->name('forum.threads.index');

        // Create a thread in a forum (POST /api/v1/forums/{forum}/threads)
        Route::post('threads', [ForumApiController::class, 'storeThread'])->name('forum.threads.store');

        // Toggle is_public for a forum (POST /api/v1/forums/{forum}/toggle-public)
        Route::post('toggle-public', [ForumApiController::class, 'togglePublic'])->name('forum.toggle-public');

        // Toggle is_pinned for a thread (POST /api/v1/forums/{forum}/threads/{thread}/toggle-pin)
        Route::post('threads/{thread}/toggle-pin', [ForumApiController::class, 'toggleThreadPin'])->name('forum.threads.toggle-pin');

        // Toggle is_locked for a thread (POST /api/v1/forums/{forum}/threads/{thread}/toggle-lock)
        Route::post('threads/{thread}/toggle-lock', [ForumApiController::class, 'toggleThreadLock'])->name('forum.threads.toggle-lock');
    });
    Route::get('threads/{thread}', [ForumApiController::class, 'showThread'])->name('forum.threads.show');
