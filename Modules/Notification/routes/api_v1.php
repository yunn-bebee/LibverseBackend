<?php


use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Modules\Notification\App\Http\Controller\NotificationApiController;
use Modules\Notification\App\Notifications\ExampleNotification;
use Modules\Notification\App\Notifications\GenericNotification;



Route::middleware(['auth:sanctum'])->prefix('notifications')->group(function () {
    Route::get('/', [NotificationApiController::class, 'index']);
    Route::get('/counts', [NotificationApiController::class, 'counts']);
    Route::post('/{id}/read', [NotificationApiController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationApiController::class, 'markAllAsRead']);
    Route::delete('/{id}', [NotificationApiController::class, 'destroy']);
    Route::delete('/', [NotificationApiController::class, 'clearAll']);
    Route::put('/preferences', [NotificationApiController::class, 'updatePreferences']);
});


Route::get('/test-notification', function () {
    // Find a user to notify (e.g., ID=23)
    $user = User::find(23);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    // Send notification
    $user->notify(new GenericNotification(
        $user,
        "New Feature 🚀",
        "We just launched a new feature in Libiverse!",
        url('/features'),
        "Check it out"
    ));

    return response()->json(['success' => 'Notification sent successfully']);
});
