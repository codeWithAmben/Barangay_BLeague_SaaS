<?php

namespace App\Enums;

enum GameEventType: string
{
    case Point1 = 'pt_1';
    case Point2 = 'pt_2';
    case Point3 = 'pt_3';
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

    public function pointsValue(): int
    {
        return match ($this) {
            self::Point1 => 1,
            self::Point2 => 2,
            self::Point3 => 3,
            default => 0,
        };
    }
}
