<?php

namespace Modules\Notification\App\Services;

use Modules\Notification\App\Contracts\NotificationServiceInterface;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class NotificationService implements NotificationServiceInterface
{
    public function getUserNotifications(User $user, int $perPage = 15, bool $unreadOnly = false): LengthAwarePaginator
    {
        try {
            $query = Notification::where('user_id', $user->id);

            if ($unreadOnly) {
                $query->whereNull('read_at');
            }

            return $query->orderBy('created_at', 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Failed to get user notifications: ' . $e->getMessage());
            throw $e;
        }
    }

    public function markAsRead(User $user, string $notificationId): bool
    {
        try {
            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->first();

            if ($notification && is_null($notification->read_at)) {
                $notification->update(['read_at' => now()]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read: ' . $e->getMessage());
            throw $e;
        }
    }

    public function markAllAsRead(User $user): int
    {
        try {
            return Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteNotification(User $user, string $notificationId): bool
    {
        try {
            return (bool) Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete notification: ' . $e->getMessage());
            throw $e;
        }
    }

    public function clearAllNotifications(User $user): int
    {
        try {
            return Notification::where('user_id', $user->id)
                ->delete();
        } catch (\Exception $e) {
            Log::error('Failed to clear all notifications: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getNotificationCount(User $user, bool $unreadOnly = true): int
    {
        try {
            $query = Notification::where('user_id', $user->id);

            if ($unreadOnly) {
                $query->whereNull('read_at');
            }

            return $query->count();
        } catch (\Exception $e) {
            Log::error('Failed to get notification count: ' . $e->getMessage());
            throw $e;
        }
    }
}
