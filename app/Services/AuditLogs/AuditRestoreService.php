<?php

namespace App\Services\AuditLogs;

use App\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Services\Contracts\AuditLogs\AuditRestoreServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AuditRestoreService implements AuditRestoreServiceInterface
{
    public function __construct(
        private readonly AuditLogServiceInterface $audit,
    ) {}

    public function restore(Model $model): bool
    {
        $before = ['deleted_at' => optional($model->deleted_at)->toDateTimeString()];
        $ok = (bool) $model->restore();

        if (! $ok) {
            return false;
        }

        $after = ['deleted_at' => null];
        $action = strtolower(class_basename($model)) . '.restored';

        try {
            $this->audit->record(
                action: $action,
                subject: $model,
                changesOld: $before,
                changesNew: $after,
                meta: ['source' => 'audit.restore.service'],
            );
        } catch (\Throwable $e) {
            Log::warning('audit.record_failed', [
                'action' => $action,
                'target' => ['type' => get_class($model), 'id' => $model->getKey()],
                'error' => $e->getMessage(),
            ]);
        }

        return true;
    }
}
