<?php

namespace App\Modules\GSO\Support;

final class ParStatuses
{
    public const DRAFT = 'draft';
    public const SUBMITTED = 'submitted';
    public const FINALIZED = 'finalized';
    public const CANCELLED = 'cancelled';

    public static function values(): array
    {
        return [
            self::DRAFT,
            self::SUBMITTED,
            self::FINALIZED,
            self::CANCELLED,
        ];
    }

    public static function labels(): array
    {
        return [
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::FINALIZED => 'Finalized',
            self::CANCELLED => 'Cancelled',
        ];
    }
}
