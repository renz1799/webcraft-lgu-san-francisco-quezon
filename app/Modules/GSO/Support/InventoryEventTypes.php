<?php

namespace App\Modules\GSO\Support;

final class InventoryEventTypes
{
    public const ACQUIRED = 'acquired';
    public const ISSUED = 'issued';
    public const TRANSFERRED_OUT = 'transferred_out';
    public const TRANSFERRED_IN = 'transferred_in';
    public const RETURNED = 'returned';
    public const DISPOSED = 'disposed';
    public const REPAIRED = 'repaired';
    public const CREATED_FROM_INSPECTION = 'created_from_inspection';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::ACQUIRED,
            self::ISSUED,
            self::TRANSFERRED_OUT,
            self::TRANSFERRED_IN,
            self::RETURNED,
            self::DISPOSED,
            self::REPAIRED,
            self::CREATED_FROM_INSPECTION,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::ACQUIRED => 'Acquired',
            self::ISSUED => 'Issued',
            self::TRANSFERRED_OUT => 'Transferred (Out)',
            self::TRANSFERRED_IN => 'Transferred (In)',
            self::RETURNED => 'Returned',
            self::DISPOSED => 'Disposed',
            self::REPAIRED => 'Repaired',
            self::CREATED_FROM_INSPECTION => 'Created From Inspection',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function manualLabels(): array
    {
        return collect(self::labels())
            ->except([self::CREATED_FROM_INSPECTION])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function increasesQuantity(): array
    {
        return [
            self::ACQUIRED,
            self::TRANSFERRED_IN,
            self::RETURNED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function decreasesQuantity(): array
    {
        return [
            self::ISSUED,
            self::TRANSFERRED_OUT,
            self::DISPOSED,
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function metadataOnly(): array
    {
        return [
            self::REPAIRED,
            self::CREATED_FROM_INSPECTION,
        ];
    }
}
