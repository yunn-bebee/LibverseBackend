<?php

namespace App\Access;

use App\Enums\UserRole;

class Permissions
{
    public static function getPermissions(): array
    {
        return [
            UserRole::ADMIN->value => [
                // Forum permissions
                'create-forum',
                'edit-forum',
                'delete-forum',
                'toggle-public-forum',
                // Thread permissions
                'create-thread',
                'edit-thread',
                'delete-thread',
                'toggle-pin-thread',
                'toggle-lock-thread',
                'view-thread',
                // Post permissions
                'create-post',
                'edit-post',
                'delete-post',
                'like-post',
                'save-post',
                'comment-post',
                'flag-post',
                'upload-media-post',
                // User management
                'manage-users',
                'ban-user',
                'approve-user',
                'moderate-content',
                'access-admin-dashboard',
                // Other modules
                'manage-challenges',
                'manage-notifications',
                'manage-events',
                'manage-mentions',
                'manage-badges',
                'manage-books',
            ],
            UserRole::MODERATOR->value => [
                // Forum permissions
                'create-forum',
                'edit-forum',
                'delete-forum',
                'toggle-public-forum',
                // Thread permissions
                'create-thread',
                'edit-thread',
                'delete-thread',
                'toggle-pin-thread',
                'toggle-lock-thread',
                'view-thread',
                // Post permissions
                'create-post',
                'edit-post',
                'delete-post',
                'flag-post',
                'upload-media-post',
                // Moderation
                'moderate-content',
                'ban-user',
                'approve-user',
            ],
            UserRole::MEMBER->value => [
                // Thread permissions
                'create-thread',
                'view-thread',
                // Post permissions
                'create-post',
                'edit-post', // Only own posts, enforced in service
                'like-post',
                'save-post',
                'comment-post',
                'upload-media-post',
                // Other modules (read-only or basic actions)
                'view-challenges',
                'view-notifications',
                'view-events',
                'view-mentions',
                'view-badges',
                'view-books',
                'create-book',
            ],
        ];
    }

    public static function hasPermission(string $role, string $permission): bool
    {
        return in_array($permission, self::getPermissions()[$role] ?? []);
    }
}
