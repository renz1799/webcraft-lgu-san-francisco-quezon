<?php

namespace App\Modules\GSO\Support;

final class InspectionStatuses
{
    public const DRAFT = 'draft';
    public const SUBMITTED = 'submitted';
    public const PENDING = 'pending';
    public const RETURNED = 'returned';
    public const APPROVED = 'approved';

    public static function values(): array
    {
        return [
            self::DRAFT,
            self::SUBMITTED,
            self::PENDING,
            self::RETURNED,
            self::APPROVED,
        ];
    }

    public static function labels(): array
    {
        return [
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::PENDING => 'Pending',
            self::RETURNED => 'Returned',
            self::APPROVED => 'Approved',
        ];
    }
}
