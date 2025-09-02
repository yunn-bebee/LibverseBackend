<?php

use App\Mail\LibiverseEmail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Modules\Auth\App\Http\Controller\Admin\ApprovalController;
use Modules\Auth\App\Http\Controller\AuthApiController;
use Modules\Notification\App\Notifications\ExampleNotification;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/login', [AuthApiController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout']);

        // Admin approval routes
        Route::prefix('admin')->group(function () {
            Route::get('/pending-users', [ApprovalController::class, 'pendingUsers']);
            Route::post('/approve-user/{user}', [ApprovalController::class, 'approveUser']);
            Route::delete('/reject-user/{user}', [ApprovalController::class, 'rejectUser']);
        });
    });
});
