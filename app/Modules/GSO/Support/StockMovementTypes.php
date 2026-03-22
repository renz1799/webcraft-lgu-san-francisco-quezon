<?php

namespace App\Modules\GSO\Support;

class StockMovementTypes
{
    public const IN = 'in';

    public const ISSUE = 'issue';

    public const RESTORE = 'restore';

    public const ADJUST_IN = 'adjust_in';

    public const ADJUST_OUT = 'adjust_out';

    public const ADJUST_SET = 'adjust_set';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::IN,
            self::ISSUE,
            self::RESTORE,
            self::ADJUST_IN,
            self::ADJUST_OUT,
            self::ADJUST_SET,
        ];
    }

    public static function label(?string $value): string
    {
        return match (self::normalize($value)) {
            self::IN => 'Receipt',
            self::ISSUE => 'Issue',
            self::RESTORE => 'Restore',
            self::ADJUST_IN => 'Adjust In',
            self::ADJUST_OUT => 'Adjust Out',
            self::ADJUST_SET => 'Adjust Set',
            default => 'Movement',
        };
    }

    public static function normalize(?string $value): string
    {
        return trim(strtolower((string) $value));
    }
}
