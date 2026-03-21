<?php

namespace App\Core\Builders\Contracts\AuditLogs;

use App\Core\Data\AuditLogs\AuditLogPrintData;
use Illuminate\Support\Collection;

interface AuditLogPrintReportBuilderInterface
{
    /**
     * @param  Collection<int, mixed>  $logs
     * @param  array<string, mixed>  $filters
     */
    public function build(Collection $logs, array $filters): AuditLogPrintData;
}
