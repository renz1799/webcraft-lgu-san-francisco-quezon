<?php

namespace App\Core\Services\Contracts\AuditLogs;

use Illuminate\Database\Eloquent\Model;

interface AuditRestoreServiceInterface
{
    public function restore(Model $model): bool;
}
