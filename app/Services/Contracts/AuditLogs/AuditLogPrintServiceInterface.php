<?php

namespace App\Services\Contracts\AuditLogs;

interface AuditLogPrintServiceInterface
{
    public function buildReport(array $filters): array;

    public function generatePdf(array $filters): string;
}
