<?php

namespace App\Modules\GSO\Support;

class AirStatuses
{
    public const DRAFT = 'draft';

    public const SUBMITTED = 'submitted';

    public const IN_PROGRESS = 'in_progress';

    public const INSPECTED = 'inspected';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return [
            self::DRAFT,
            self::SUBMITTED,
            self::IN_PROGRESS,
            self::INSPECTED,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::IN_PROGRESS => 'In Progress',
            self::INSPECTED => 'Inspected',
        ];
    }

    public static function label(?string $value): string
    {
        $value = trim((string) ($value ?? ''));

        return self::labels()[$value] ?? ($value !== '' ? ucwords(str_replace('_', ' ', $value)) : 'Unknown');
    }
}
