<?php

use Illuminate\Support\Facades\Route;
use Modules\User\App\Http\Controller\UserApiController;
use Modules\User\App\Http\Controller\ProfileApiController;

Route::apiResource('user',UserApiController::class);
// Add these routes
Route::prefix('profile')->group(function () {
    Route::get('/', [ProfileApiController::class, 'show']);
    Route::put('/', [ProfileApiController::class, 'update']);
    Route::delete('/', [ProfileApiController::class, 'destroy']);
});