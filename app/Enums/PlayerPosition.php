<?php

namespace App\Enums;

enum PlayerPosition: string
{
    case PointGuard = 'PG';
    case ShootingGuard = 'SG';
    case SmallForward = 'SF';
    case PowerForward = 'PF';
    case Center = 'C';

    public function label(): string
    {
        return match ($this) {
            self::PointGuard => 'Point Guard (PG)',
            self::ShootingGuard => 'Shooting Guard (SG)',
            self::SmallForward => 'Small Forward (SF)',
            self::PowerForward => 'Power Forward (PF)',
            self::Center => 'Center (C)',
        };
    }
}
