<?php

namespace Modules\Notification\App\Http\Controller;

use App\Http\Controllers\Controller;
use Modules\Notification\App\Contracts\NotificationServiceInterface;
use Modules\Notification\App\Http\Requests\NotificationRequest;
use Modules\Notification\App\Resources\NotificationApiResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationApiController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationServiceInterface $notificationService)
    {
        $this->notificationService = $notificationService;

    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $unreadOnly = $request->get('unread_only', false);
        $perPage = $request->get('per_page', 15);

        $notifications = $this->notificationService->getUserNotifications($user, $perPage, $unreadOnly);

        return response()->json([
            'success' => true,
            'data' => NotificationApiResource::collection($notifications->items()),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'unread_count' => $this->notificationService->getNotificationCount($user, true),
            ]
        ]);
    }

        public function markAsRead($id)
    {
        $user = Auth::user();
        $success = $this->notificationService->markAsRead($user, $id);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found or already read.'
        ], 404);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsRead($user);

        return response()->json([
            'success' => true,
            'message' => "{$count} notifications marked as read.",
            'data' => ['count' => $count]
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $deleted = $this->notificationService->deleteNotification($user, $id);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Notification not found.'
        ], 404);
    }
public function clearAll()
{
    $user = Auth::user();
    $deletedCount = $this->notificationService->clearAllNotifications($user);

    return response()->json([
        'success' => true,
        'message' => "All notifications cleared successfully.",
        'data' => ['deleted_count' => $deletedCount]
    ]);
}
    public function counts()
    {
        $user = Auth::user();
        $unreadCount = $this->notificationService->getNotificationCount($user, true);
        $totalCount = $this->notificationService->getNotificationCount($user, false);

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $unreadCount,
                'total_count' => $totalCount
            ]
        ]);
    }

    public function updatePreferences(NotificationRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated.',
            'data' => [
                'email_notifications' => $user->email_notifications,
                'push_notifications' => $user->push_notifications
            ]
        ]);
    }
}
