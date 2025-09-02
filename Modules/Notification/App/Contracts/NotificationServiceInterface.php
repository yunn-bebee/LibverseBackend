<?php

namespace Modules\Notification\App\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationServiceInterface
{
    public function getUserNotifications(User $user, int $perPage = 15, bool $unreadOnly = false): LengthAwarePaginator;
    public function markAsRead(User $user, string $notificationId): bool;
    public function markAllAsRead(User $user): int;
    public function deleteNotification(User $user, string $notificationId): bool;
    public function clearAllNotifications(User $user): int;
    public function getNotificationCount(User $user, bool $unreadOnly = true): int;
}
