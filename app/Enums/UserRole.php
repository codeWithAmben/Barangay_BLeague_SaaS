<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Commissioner = 'commissioner';
    case Scorer = 'scorer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Commissioner => 'League Commissioner',
            self::Scorer => 'Table Official / Scorer',
        };
    }
}
