<?php

namespace App\Data\AuditLogs;

class AuditLogPrintData
{
    /**
     * @param array<string, mixed> $filters
     * @param array<int, array<string, mixed>> $rows
     */
    public function __construct(
        public readonly string $title,
        public readonly array $filters,
        public readonly array $rows,
        public readonly int $total,
        public readonly string $generatedAt,
    ) {
    }
}