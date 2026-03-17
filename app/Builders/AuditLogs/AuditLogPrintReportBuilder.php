<?php

namespace App\Builders\AuditLogs;

use App\Data\AuditLogs\AuditLogPrintData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AuditLogPrintReportBuilder
{
    public function build(Collection $logs, array $filters): AuditLogPrintData
    {
        $rows = $logs->map(function ($log): array {
            return [
                'datetime' => optional($log->created_at)?->format('Y-m-d h:i A'),
                'module_name' => $log->module_name ?: '-',
                'action' => $log->action ?: '-',
                'message' => $log->message ?: '-',
                'actor_name' => $this->resolveActorName($log),
                'subject_type' => $log->subject_type ? class_basename((string) $log->subject_type) : '-',
                'subject_id' => $log->subject_id ?: '-',
            ];
        })->values()->all();

        return new AuditLogPrintData(
            title: 'Audit Log Report',
            filters: $filters,
            rows: $rows,
            total: count($rows),
            generatedAt: Carbon::now()->format('Y-m-d h:i A'),
        );
    }

    private function resolveActorName($log): string
    {
        $actor = $log->actor;

        if (! $actor) {
            return 'System';
        }

        if (isset($actor->name) && filled($actor->name)) {
            return $actor->name;
        }

        if (isset($actor->full_name) && filled($actor->full_name)) {
            return $actor->full_name;
        }

        if (isset($actor->username) && filled($actor->username)) {
            return $actor->username;
        }

        return class_basename($log->actor_type ?: get_class($actor));
    }
}