<?php

namespace App\Builders\Contracts\AuditLogs;

use App\Models\AuditLog;

interface AuditLogDatatableRowBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function build(AuditLog $log): array;
}
