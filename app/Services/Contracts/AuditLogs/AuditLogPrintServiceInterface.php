<?php

namespace App\Services\Contracts\AuditLogs;

use App\Data\AuditLogs\AuditLogPrintData;

interface AuditLogPrintServiceInterface
{
    public function buildReport(array $filters): array;

    public function generatePdf(array $filters): string;
}