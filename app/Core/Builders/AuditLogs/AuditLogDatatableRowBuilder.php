<?php

namespace App\Core\Builders\AuditLogs;

use App\Core\Builders\Contracts\AuditLogs\AuditLogDatatableRowBuilderInterface;
use App\Core\Models\AuditLog;
use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Models\User;

class AuditLogDatatableRowBuilder implements AuditLogDatatableRowBuilderInterface
{
    public function build(AuditLog $log): array
    {
        $actor = $log->actor;
        $subject = $log->subject;
        $old = (array) ($log->changes_old ?? []);
        $new = (array) ($log->changes_new ?? []);

        $subjectTypeShort = $this->subjectTypeShort($log->subject_type);
        $isTrashed = $subject && method_exists($subject, 'trashed') && $subject->trashed();
        $maybeDeletedAction = str_ends_with((string) $log->action, '.deleted');

        return [
            'id' => (string) $log->id,
            'created_at_iso' => $log->created_at?->toIso8601String(),
            'created_at_text' => $log->created_at?->format('M d, Y g:i A'),
            'actor_name' => $actor instanceof User
                ? $this->buildUserDisplayName($actor)
                : ($actor ? ($actor->username ?? $actor->email ?? 'User') : '-'),
            'actor_id' => $log->actor_id,
            'action' => (string) ($log->action ?? ''),
            'subject_label' => $this->buildSubjectLabel($log),
            'subject_id' => $log->subject_id,
            'subject_type_short' => $subjectTypeShort,
            'subject_is_deleted' => (bool) $isTrashed,
            'subject_show_restore' => (bool) (
                ($log->subject_type && $log->subject_id) &&
                ($isTrashed || $maybeDeletedAction) &&
                $subjectTypeShort
            ),
            'request' => trim((string) ($log->request_method ?? '') . ' ' . (string) ($log->request_url ?? '')),
            'ip' => (string) ($log->ip ?? ''),
            'message' => $log->message,
            'changes_old' => $old,
            'changes_new' => $new,
            'meta' => $log->meta,
            'user_agent' => $log->user_agent,
        ];
    }

    private function buildSubjectLabel(AuditLog $log): string
    {
        $subject = $log->subject;
        $old = (array) ($log->changes_old ?? []);
        $new = (array) ($log->changes_new ?? []);

        $type = $log->subject_type ? class_basename($log->subject_type) : null;

        if ($type === 'User') {
            $name = $this->buildUserDisplayName($subject instanceof User ? $subject : null, $old, $new);

            return $type ? trim($type . ($name ? ' : ' . $name : '')) : '-';
        }

        $name = $subject?->name
            ?? ($old['name'] ?? null)
            ?? ($new['name'] ?? null);

        $page = $subject?->page
            ?? ($old['page'] ?? null)
            ?? ($new['page'] ?? null);

        return $type
            ? trim($type . ($name ? ' : ' . $name : '') . ($page ? ' - ' . $page : ''))
            : '-';
    }

    private function buildUserDisplayName(?User $user, array $old = [], array $new = []): string
    {
        $profileName = trim((string) ($user?->profile?->full_name ?? ''));
        if ($profileName !== '') {
            return $profileName;
        }

        $profilePayloadName = $this->buildProfileNameFromArray($new['profile'] ?? null)
            ?: $this->buildProfileNameFromArray($old['profile'] ?? null);

        if ($profilePayloadName !== '') {
            return $profilePayloadName;
        }

        return (string) (
            $user?->username
            ?? $user?->email
            ?? ($new['username'] ?? null)
            ?? ($old['username'] ?? null)
            ?? ($new['email'] ?? null)
            ?? ($old['email'] ?? null)
            ?? 'User'
        );
    }

    private function buildProfileNameFromArray(mixed $profileData): string
    {
        if (! is_array($profileData)) {
            return '';
        }

        return trim(implode(' ', array_filter([
            $profileData['first_name'] ?? null,
            $profileData['middle_name'] ?? null,
            $profileData['last_name'] ?? null,
            $profileData['name_extension'] ?? null,
        ])));
    }

    private function subjectTypeShort(?string $subjectType): ?string
    {
        return match ($subjectType) {
            User::class => 'user',
            Permission::class => 'permission',
            Role::class => 'role',
            default => null,
        };
    }
}

