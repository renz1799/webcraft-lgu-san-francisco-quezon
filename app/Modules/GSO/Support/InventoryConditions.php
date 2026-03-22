<?php

namespace App\Modules\GSO\Support;

final class InventoryConditions
{
    public const BRAND_NEW = 'brand_new';
    public const GOOD = 'good';
    public const FAIR = 'fair';
    public const POOR = 'poor';
    public const DAMAGED = 'damaged';

    public static function values(): array
    {
        return [
            self::BRAND_NEW,
            self::GOOD,
            self::FAIR,
            self::POOR,
            self::DAMAGED,
        ];
    }

    public static function labels(): array
    {
        return [
            self::BRAND_NEW => 'Brand New',
            self::GOOD => 'Good',
            self::FAIR => 'Fair',
            self::POOR => 'Poor',
            self::DAMAGED => 'Damaged',
        ];
    }
}
