<?php

namespace App\Modules\GSO\Support;

final class InventoryFileTypes
{
    public const PHOTO = 'photo';
    public const SERIAL_PHOTO = 'serial_photo';
    public const BOX_PHOTO = 'box_photo';
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
            self::SERIAL_PHOTO,
            self::BOX_PHOTO,
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
            self::SERIAL_PHOTO => 'Serial Photo',
            self::BOX_PHOTO => 'Box Photo',
            self::PDF => 'PDF',
            self::DOCUMENT => 'Document',
            self::RECEIPT => 'Receipt',
            self::PROPERTY_CARD => 'Property Card',
            self::OTHER => 'Other',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function airImageValues(): array
    {
        return [
            self::PHOTO,
            self::SERIAL_PHOTO,
            self::BOX_PHOTO,
            self::OTHER,
        ];
    }
}
