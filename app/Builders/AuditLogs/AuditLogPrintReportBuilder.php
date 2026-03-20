<?php

namespace App\Builders\AuditLogs;

use App\Data\AuditLogs\AuditLogPrintData;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AuditLogPrintReportBuilder
{
    public function build(Collection $logs, array $filters): AuditLogPrintData
    {
        $rows = $logs->map(function ($log): array {
            return [
                'datetime' => optional($log->created_at)?->format('Y-m-d h:i A'),
                'module' => $this->resolveModuleLabel($log),
                'action' => $log->action ?: '-',
                'message' => $log->message ?: '-',
                'actor_name' => $this->resolveActorName($log),
                'subject' => $this->resolveSubjectLabel($log),
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

        $profileName = trim((string) ($actor->profile->full_name ?? ''));
        if ($profileName !== '') {
            return $profileName;
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

    private function resolveModuleLabel($log): string
    {
        $module = $log->module;

        $name = trim((string) ($module->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $code = trim((string) ($module->code ?? ''));
        if ($code !== '') {
            return $code;
        }

        return $log->module_id ? (string) $log->module_id : 'Legacy / Unscoped';
    }

    private function resolveSubjectLabel($log): string
    {
        $type = $this->resolveSubjectTypeLabel($log->subject_type);
        $displayLabel = trim((string) data_get($log, 'meta.display.subject_label', ''));

        if ($displayLabel !== '') {
            return $this->appendSubjectId("{$type}: {$displayLabel}", $log->subject_id);
        }

        $subject = $log->subject;
        $label = match (true) {
            $subject instanceof User => $this->resolveUserName($subject, $log),
            $subject instanceof Permission => $this->resolvePermissionName($subject, $log),
            $subject instanceof Role => $this->resolveRoleName($subject, $log),
            default => trim((string) (
                data_get($log, 'changes_new.name')
                ?? data_get($log, 'changes_old.name')
                ?? ''
            )),
        };

        $subjectText = $label !== '' ? "{$type}: {$label}" : $type;

        return $this->appendSubjectId($subjectText, $log->subject_id);
    }

    private function resolveUserName(User $user, mixed $log): string
    {
        $profileName = trim((string) ($user->profile->full_name ?? ''));
        if ($profileName !== '') {
            return $profileName;
        }

        return trim((string) (
            $user->username
            ?? $user->email
            ?? data_get($log, 'changes_new.username')
            ?? data_get($log, 'changes_old.username')
            ?? data_get($log, 'changes_new.email')
            ?? data_get($log, 'changes_old.email')
            ?? 'User'
        ));
    }

    private function resolvePermissionName(Permission $permission, mixed $log): string
    {
        $name = trim((string) (
            $permission->name
            ?? data_get($log, 'changes_new.name')
            ?? data_get($log, 'changes_old.name')
            ?? ''
        ));

        $page = trim((string) (
            $permission->page
            ?? data_get($log, 'changes_new.page')
            ?? data_get($log, 'changes_old.page')
            ?? ''
        ));

        if ($name !== '' && $page !== '') {
            return "{$name} - {$page}";
        }

        return $name !== '' ? $name : $page;
    }

    private function resolveRoleName(Role $role, mixed $log): string
    {
        return trim((string) (
            $role->name
            ?? data_get($log, 'changes_new.name')
            ?? data_get($log, 'changes_old.name')
            ?? 'Role'
        ));
    }

    private function resolveSubjectTypeLabel(?string $subjectType): string
    {
        return match ($subjectType) {
            User::class => 'User',
            Permission::class => 'Permission',
            Role::class => 'Role',
            default => $subjectType ? class_basename($subjectType) : 'Subject',
        };
    }

    private function appendSubjectId(string $subject, mixed $subjectId): string
    {
        $label = trim($subject);
        $id = trim((string) $subjectId);

        if ($id === '') {
            return $label !== '' ? $label : '-';
        }

        return trim($label !== '' ? "{$label} [{$id}]" : "[{$id}]");
    }
}
