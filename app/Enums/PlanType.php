<?php

namespace App\Enums;

enum PlanType: string
{
    case Trial = 'trial';
    case BarangayPro = 'barangay_pro';
    case Municipality = 'municipality';

    public function label(): string
    {
        return match ($this) {
            self::Trial => 'Free / Trial',
            self::BarangayPro => 'Barangay Pro',
            self::Municipality => 'Municipality / Enterprise',
        };
    }

    public function maxTournaments(): ?int
    {
        return match ($this) {
            self::Trial => 1,
            self::BarangayPro, self::Municipality => null,
        };
    }

    public function maxTeamsPerTournament(): ?int
    {
        return match ($this) {
            self::Trial => 6,
            self::BarangayPro, self::Municipality => null,
        };
    }
}
