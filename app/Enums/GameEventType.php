<?php

namespace App\Enums;

enum GameEventType: string
{
    case Point1 = 'pt_1';
    case Point2 = 'pt_2';
    case Point3 = 'pt_3';
    case Missed1 = 'miss_1';
    case Missed2 = 'miss_2';
    case Missed3 = 'miss_3';
    case MissedShot = 'miss';
    case ReboundOff = 'reb_off';
    case ReboundDef = 'reb_def';
    case Assist = 'ast';
    case Steal = 'stl';
    case Block = 'blk';
    case Turnover = 'to';
    case FoulPersonal = 'foul_p';
    case FoulTechnical = 'foul_t';
    case FoulUnsportsmanlike = 'foul_u';
    case FoulDisqualifying = 'foul_d';
    case SubIn = 'sub_in';
    case SubOut = 'sub_out';
    case Timeout = 'timeout';
    case Violation = 'violation';

    public function pointsValue(): int
    {
        return match ($this) {
            self::Point1 => 1,
            self::Point2 => 2,
            self::Point3 => 3,
            default => 0,
        };
    }

    public function isFoul(): bool
    {
        return in_array($this, [
            self::FoulPersonal,
            self::FoulTechnical,
            self::FoulUnsportsmanlike,
            self::FoulDisqualifying,
        ], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Point1 => '+1 FT Made',
            self::Point2 => '+2 FG Made',
            self::Point3 => '+3 FG Made',
            self::Missed1 => 'Missed FT',
            self::Missed2 => 'Missed 2PT',
            self::Missed3 => 'Missed 3PT',
            self::MissedShot => 'Missed Shot',
            self::ReboundOff => 'Offensive Rebound',
            self::ReboundDef => 'Defensive Rebound',
            self::Assist => 'Assist',
            self::Steal => 'Steal',
            self::Block => 'Block',
            self::Turnover => 'Turnover',
            self::FoulPersonal => 'Personal Foul',
            self::FoulTechnical => 'Technical Foul (T)',
            self::FoulUnsportsmanlike => 'Unsportsmanlike Foul (U)',
            self::FoulDisqualifying => 'Disqualifying Foul (D)',
            self::SubIn => 'Sub In',
            self::SubOut => 'Sub Out',
            self::Timeout => 'Timeout Called',
            self::Violation => 'Team / Ball Violation',
        };
    }
}
