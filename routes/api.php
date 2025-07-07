<?php // routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json(['message' => 'API working']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group([
    'prefix' => 'v1',
    'namespace' => 'App\Http\Controllers\Api\V1'
], function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [App\Http\Controllers\Api\V1\Auth\AuthController::class, 'register']);
        Route::post('login', [App\Http\Controllers\Api\V1\Auth\AuthController::class, 'login']);
        Route::post('forgot-password', [App\Http\Controllers\Api\V1\Auth\AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [App\Http\Controllers\Api\V1\Auth\AuthController::class, 'resetPassword']);
        
        // Protected auth routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [App\Http\Controllers\Api\V1\Auth\AuthController::class, 'logout']);
            Route::get('profile', [App\Http\Controllers\Api\V1\Profile\ProfileController::class, 'show']);
            Route::put('profile', [App\Http\Controllers\Api\V1\Profile\ProfileController::class, 'update']);
        });
    });
    
    // Admin approval routes
    Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin,moderator'])->group(function () {
        Route::get('pending-users', [App\Http\Controllers\Api\V1\Admin\ApprovalController::class, 'pendingUsers']);
        Route::post('approve-user/{user}', [App\Http\Controllers\Api\V1\Admin\ApprovalController::class, 'approveUser']);
        Route::delete('reject-user/{user}', [App\Http\Controllers\Api\V1\Admin\ApprovalController::class, 'rejectUser']);
    });
    
 
});