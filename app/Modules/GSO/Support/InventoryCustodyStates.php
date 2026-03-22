<?php

namespace App\Modules\GSO\Support;

final class InventoryCustodyStates
{
    public const POOL = 'pool';
    public const ISSUED = 'issued';

    public static function values(): array
    {
        return [
            self::POOL,
            self::ISSUED,
        ];
    }

    public static function labels(): array
    {
        return [
            self::POOL => 'Pool',
            self::ISSUED => 'Issued',
        ];
    }
}
