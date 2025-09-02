<?php


use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Modules\Notification\App\Http\Controller\NotificationApiController;
use Modules\Notification\App\Notifications\ExampleNotification;


Route::middleware(['auth:sanctum'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationApiController::class, 'index']);
    Route::get('/counts', [NotificationApiController::class, 'counts']);
    Route::post('/{id}/read', [NotificationApiController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationApiController::class, 'markAllAsRead']);
    Route::delete('/{id}', [NotificationApiController::class, 'destroy']);
    Route::delete('/', [NotificationApiController::class, 'clearAll']);
    Route::put('/preferences', [NotificationApiController::class, 'updatePreferences']);
});

