<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Kitchen = 'kitchen';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Kitchen => 'Kitchen',
        };
    }
}
