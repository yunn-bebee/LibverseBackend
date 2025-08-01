<?php
namespace Modules\Forum;

use Illuminate\Support\Facades\Route;
use Modules\Forum\App\Http\Controller\ForumApiController;

Route::get('forum', [ForumApiController::class, 'index'])->name('forum.index');
Route::post('forum', [ForumApiController::class, 'store'])->name('forum.store');
Route::get('forum/{forum}', [ForumApiController::class, 'show'])->name('forum.show');
Route::put('forum/{forum}', [ForumApiController::class, 'update'])->name('forum.update');
Route::delete('forum/{forum}', [ForumApiController::class, 'destroy'])->name('forum.destroy');
Route::get('forum/{forum}/threads', [ForumApiController::class, 'getThreads'])->name('forum.threads.index');
Route::post('forum/{forum}/threads', [ForumApiController::class, 'storeThread'])->name('forum.threads.store');
