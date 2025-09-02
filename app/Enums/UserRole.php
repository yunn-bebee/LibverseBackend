<?php

namespace App\Enums;

enum UserRole: string
{
    case MEMBER = 'Member';
    case MODERATOR = 'Moderator';
    case ADMIN = 'Admin';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    public static function options(): array
    {
        return [
            self::MEMBER->value => 'Regular Member',
            self::MODERATOR->value => 'Content Moderator',
            self::ADMIN->value => 'System Administrator',
        ];
    }

    public function label(): string
    {
        return match($this) {
            self::MEMBER => 'Regular Member',
            self::MODERATOR => 'Content Moderator',
            self::ADMIN => 'System Administrator',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isModeratorOrHigher(): bool
    {
        return $this === self::MODERATOR || $this === self::ADMIN;
    }
}
