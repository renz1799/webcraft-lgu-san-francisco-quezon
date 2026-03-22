<?php

namespace App\Modules\GSO\Support;

final class InventoryStatuses
{
    public const SERVICEABLE = 'serviceable';
    public const FOR_REPAIR = 'for_repair';
    public const UNDER_REPAIR = 'under_repair';
    public const UNSERVICEABLE = 'unserviceable';
    public const DISPOSED = 'disposed';
    public const LOST = 'lost';
    public const TRANSFERRED = 'transferred';
    public const RETURNED = 'returned';

    public static function values(): array
    {
        return [
            self::SERVICEABLE,
            self::FOR_REPAIR,
            self::UNDER_REPAIR,
            self::UNSERVICEABLE,
            self::DISPOSED,
            self::LOST,
            self::TRANSFERRED,
            self::RETURNED,
        ];
    }

    public static function labels(): array
    {
        return [
            self::SERVICEABLE => 'Serviceable',
            self::FOR_REPAIR => 'For Repair',
            self::UNDER_REPAIR => 'Under Repair',
            self::UNSERVICEABLE => 'Unserviceable',
            self::DISPOSED => 'Disposed',
            self::LOST => 'Lost',
            self::TRANSFERRED => 'Transferred',
            self::RETURNED => 'Returned',
        ];
    }
}
