<?php

namespace App\Enums;

enum TournamentCategory: string
{
    case Midget = 'midget';
    case Junior = 'junior';
    case Senior = 'senior';
    case Open = 'open';
    case FortyUp = '40_up';

    public function label(): string
    {
        return match ($this) {
            self::Midget => 'Midget',
            self::Junior => 'Junior',
            self::Senior => 'Senior',
            self::Open => 'Open',
            self::FortyUp => '40-Up',
        };
    }
}
