<?php

namespace App\Modules\GSO\Support;

final class InventoryFileTypes
{
    public const PHOTO = 'photo';
    public const PDF = 'pdf';
    public const DOCUMENT = 'document';
    public const RECEIPT = 'receipt';
    public const PROPERTY_CARD = 'property_card';
    public const OTHER = 'other';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::PHOTO,
            self::PDF,
            self::DOCUMENT,
            self::RECEIPT,
            self::PROPERTY_CARD,
            self::OTHER,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::PHOTO => 'Photo',
            self::PDF => 'PDF',
            self::DOCUMENT => 'Document',
            self::RECEIPT => 'Receipt',
            self::PROPERTY_CARD => 'Property Card',
            self::OTHER => 'Other',
        ];
    }
}
