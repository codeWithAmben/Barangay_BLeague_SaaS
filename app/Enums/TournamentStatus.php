<?php

namespace App\Enums;

enum TournamentStatus: string
{
    case Draft = 'draft';
    case Registration = 'registration';
    case Ongoing = 'ongoing';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Registration => 'Registration Open',
            self::Ongoing => 'Ongoing',
            self::Completed => 'Completed',
        };
    }
}
