<?php

namespace App\Enums;

enum TaskType: int
{
    case LEAVE = 1;
    case OBT = 2;
    case SV = 3;
    case COA = 4;
    case WFH = 5;
    case HOLIDAY = 6;

    public function listtask(): string
    {
        return match ($this) {
            self::LEAVE => 'LEAVE',
            self::OBT => 'OBT',
            self::SV => 'SITE VISIT',
            self::COA => 'COA',
            self::WFH => 'WORK FROM HOME',
            self::HOLIDAY => 'HOLIDAY',
        };
    }

    /**
     * Get the task type text from value with fallback
     */
    public static function getTextFromValue(int $value): string
    {
        try {
            return self::from($value)->listtask();
        } catch (\ValueError $e) {
            return 'UNKNOWN';
        }
    }
}
