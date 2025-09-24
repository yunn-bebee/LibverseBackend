<?php

namespace Modules\Forum;

use Illuminate\Support\Facades\Route;
use Modules\Forum\App\Http\Controller\ForumApiController;

    // Forum resource routes (index, store, show, update, destroy)
    Route::middleware('auth:sanctum')->group(function () {
        Route::resource('forums', ForumApiController::class);

    // Nested thread resource routes under forums
    Route::prefix('forums/{forum}')->group(function () {


         // Forum actions
        Route::post('toggle-public', [ForumApiController::class, 'togglePublic'])->name('forum.toggle-public');
        Route::post('join', [ForumApiController::class, 'join'])->name('forum.join');
        Route::post('leave', [ForumApiController::class, 'leave'])->name('forum.leave');
        Route::get('members', [ForumApiController::class, 'members'])->name('forum.members');
        Route::post('approve-join', [ForumApiController::class, 'approveJoinRequest'])->name('forum.approve-join');
        Route::post('reject-join', [ForumApiController::class, 'rejectJoinRequest'])->name('forum.reject-join');
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
        Route::put('threads/{thread}', [ForumApiController::class, 'updateThread'])->name('forum.threads.update');
        Route::delete('threads/{thread}', [ForumApiController::class, 'destroyThread'])->name('forum.threads.destroy');
      // Activity feed
    Route::get('activity-feed', [ForumApiController::class, 'activityFeed'])->name('forum.activity-feed');
    });
