<?php
// app/Services/Audit/AuditLogTableService.php
namespace App\Services\Audit;

use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Services\Contracts\AuditLogTableServiceInterface;

class AuditLogTableService implements AuditLogTableServiceInterface
{
    public function __construct(
        private readonly AuditLogRepositoryInterface $logs
    ) {}

    public function tableData(array $filters, int $page, int $size): array
    {
        $paginator = $this->logs->paginateForTable($filters, $page, $size);

        $data = $paginator->getCollection()->map(function ($log) {
            $actor = $log->actor;

            // ---- subject label logic (ported from Blade) ----
            $sub  = $log->subject;
            $old  = (array) ($log->changes_old ?? []);
            $new  = (array) ($log->changes_new ?? []);
            $type = $log->subject_type ? class_basename($log->subject_type) : null;

            $name = $sub->name
                ?? ($old['name'] ?? null)
                ?? ($new['name'] ?? null);

            $page = $sub->page
                ?? ($old['page'] ?? null)
                ?? ($new['page'] ?? null);

            if ($type === 'User' && ! $name) {
                $name = $sub->username
                    ?? $sub->email
                    ?? ($old['username'] ?? $old['email'] ?? ($new['username'] ?? $new['email'] ?? 'User'));
            }

            $label = $type
                ? trim($type . ($name ? ' : '.$name : '') . ($page ? ' — '.$page : ''))
                : '—';

            $isTrashed = $sub && method_exists($sub, 'trashed') && $sub->trashed();
            $maybeDeletedAction = str_ends_with((string) $log->action, '.deleted');

            $typeShort = match ($log->subject_type) {
                \App\Models\User::class       => 'user',
                \App\Models\Permission::class => 'permission',
                \App\Models\Role::class       => 'role',
                default => null,
            };

            return [
                'id' => $log->id,

                'created_at_text' => $log->created_at?->format('Y-m-d H:i:s'),

                'actor_name' => $actor
                    ? ($actor->username ?? $actor->email ?? 'User')
                    : '—',
                'actor_id' => $log->actor_id,

                'action' => $log->action,

                'subject_label' => $label,
                'subject_id' => $log->subject_id,
                'subject_type_short' => $typeShort,
                'subject_is_deleted' => (bool) $isTrashed,
                'subject_show_restore' => (bool) (
                    ($log->subject_type && $log->subject_id) &&
                    ($isTrashed || $maybeDeletedAction) &&
                    $typeShort
                ),

                'request' => trim(($log->request_method ?? '').' '.($log->request_url ?? '')),
                'ip' => $log->ip,

                // modal payloads
                'message' => $log->message,
                'changes_old' => $log->changes_old,
                'changes_new' => $log->changes_new,
                'meta' => $log->meta,
                'user_agent' => $log->user_agent,
            ];
        })->values()->all();

        return [
            'data' => $data,
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
