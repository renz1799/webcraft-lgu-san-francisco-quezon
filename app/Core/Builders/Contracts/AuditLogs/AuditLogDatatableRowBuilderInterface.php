<?php

namespace App\Core\Builders\Contracts\AuditLogs;

use App\Core\Models\AuditLog;

interface AuditLogDatatableRowBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function build(AuditLog $log): array;
}
