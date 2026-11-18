<?php

namespace App\Enums;

enum TournamentFormat: string
{
    case SingleRoundRobin = 'single_round_robin';
    case DoubleRoundRobin = 'double_round_robin';
    case GroupKnockout = 'group_knockout';
    case SingleElimination = 'single_elimination';

    public function label(): string
    {
        return match ($this) {
            self::SingleRoundRobin => 'Single Round Robin',
            self::DoubleRoundRobin => 'Double Round Robin',
            self::GroupKnockout => 'Group Stage + Knockout',
            self::SingleElimination => 'Single Elimination',
        };
    }
}
