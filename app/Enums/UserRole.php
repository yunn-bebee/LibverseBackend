<?php

namespace App\Enums;

enum UserRole: string
{
    case MEMBER = 'Member';
    case MODERATOR = 'Moderator';
    case ADMIN = 'Admin';
    
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    // Add this method for default value
    public static function default(): self
    {
        return self::MEMBER;
    }
}