<?php
// app/Repositories/Eloquent/EloquentAuditLogRepository.php

namespace App\Repositories\Eloquent;

use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog
    {
        return AuditLog::create($data);
    }

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, (int) $size);

        $recordsTotal = (clone $this->buildBaseQuery(false))->count();

        $filteredForCount = $this->buildFilteredQuery($filters, false);
        $recordsFiltered = (clone $filteredForCount)->count();

        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = $this->buildFilteredQuery($filters)
            ->forPage($page, $size)
            ->get()
            ->map(fn (AuditLog $log) => $this->mapRow($log))
            ->values()
            ->all();

        return [
            'data' => $rows,
            'last_page' => $lastPage,
            'total' => (int) $recordsFiltered,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
        ];
    }

    public function paginate(int $perPage = 50, array $filters = []): LengthAwarePaginator
    {
        return $this->buildFilteredQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateForTable(array $filters, int $page, int $size): LengthAwarePaginator
    {
        return $this->buildFilteredQuery($filters)
            ->paginate($size, ['*'], 'page', $page);
    }

    private function buildBaseQuery(bool $withRelations = true): Builder
    {
        $q = AuditLog::query()->latest('created_at');

        if (! $withRelations) {
            return $q;
        }

        return $q->with([
            'actor' => function (MorphTo $morph) {
                $morph->constrain([
                    User::class => fn (Builder $qq) => $qq->withTrashed()->select('id', 'username', 'email', 'deleted_at'),
                ]);
            },
            'subject' => function (MorphTo $morph) {
                $morph->constrain([
                    User::class => fn (Builder $qq) => $qq->withTrashed()->select('id', 'username', 'email', 'deleted_at'),
                    Permission::class => fn (Builder $qq) => $qq->withTrashed()->select('id', 'name', 'page', 'deleted_at'),
                    Role::class => fn (Builder $qq) => $qq->withTrashed()->select('id', 'name', 'deleted_at'),
                ]);
            },
        ]);
    }

    private function buildFilteredQuery(array $filters, bool $withRelations = true): Builder
    {
        $q = $this->buildBaseQuery($withRelations);

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $term = '%' . $search . '%';

            $q->where(function (Builder $qq) use ($term) {
                $qq->where('action', 'like', $term)
                    ->orWhere('message', 'like', $term)
                    ->orWhere('ip', 'like', $term)
                    ->orWhere('request_method', 'like', $term)
                    ->orWhere('request_url', 'like', $term)
                    ->orWhere('subject_id', 'like', $term)
                    ->orWhere('actor_id', 'like', $term)
                    ->orWhereHasMorph('actor', [User::class], function (Builder $aq) use ($term) {
                        $aq->withTrashed()->where('username', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    })
                    ->orWhereHasMorph('subject', [User::class], function (Builder $sq) use ($term) {
                        $sq->withTrashed()->where('username', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    })
                    ->orWhereHasMorph('subject', [Permission::class], function (Builder $sq) use ($term) {
                        $sq->withTrashed()->where('name', 'like', $term)
                            ->orWhere('page', 'like', $term);
                    })
                    ->orWhereHasMorph('subject', [Role::class], function (Builder $sq) use ($term) {
                        $sq->withTrashed()->where('name', 'like', $term);
                    });
            });
        }

        $action = trim((string) ($filters['action'] ?? ''));
        if ($action !== '') {
            $q->where('action', 'like', '%' . $action . '%');
        }

        $actorId = trim((string) ($filters['actor_id'] ?? ''));
        if ($actorId !== '') {
            $q->where('actor_id', $actorId);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $q->whereDate('created_at', '>=', $dateFrom);
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $q->whereDate('created_at', '<=', $dateTo);
        }

        $subjectTypeInput = trim((string) ($filters['subject_type'] ?? ''));
        if ($subjectTypeInput !== '') {
            $short = strtolower($subjectTypeInput);
            $map = [
                'user' => User::class,
                'permission' => Permission::class,
                'role' => Role::class,
            ];

            if (isset($map[$short])) {
                $q->where('subject_type', $map[$short]);
            } elseif (in_array($subjectTypeInput, $map, true)) {
                $q->where('subject_type', $subjectTypeInput);
            }
        }

        return $q;
    }

    private function mapRow(AuditLog $log): array
    {
        $actor = $log->actor;
        $subject = $log->subject;

        $subjectTypeShort = match ($log->subject_type) {
            User::class => 'user',
            Permission::class => 'permission',
            Role::class => 'role',
            default => null,
        };

        $isTrashed = $subject && method_exists($subject, 'trashed') && $subject->trashed();
        $maybeDeletedAction = str_ends_with((string) $log->action, '.deleted');

        return [
            'id' => (string) $log->id,
            'created_at_text' => $log->created_at?->format('Y-m-d H:i:s'),

            'actor_name' => $actor
                ? ($actor->username ?? $actor->email ?? 'User')
                : '-',
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
            'changes_old' => $log->changes_old,
            'changes_new' => $log->changes_new,
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

        $name = $subject->name
            ?? ($old['name'] ?? null)
            ?? ($new['name'] ?? null);

        $page = $subject->page
            ?? ($old['page'] ?? null)
            ?? ($new['page'] ?? null);

        if ($type === 'User' && ! $name) {
            $name = $subject->username
                ?? $subject->email
                ?? ($old['username'] ?? $old['email'] ?? ($new['username'] ?? $new['email'] ?? 'User'));
        }

        return $type
            ? trim($type . ($name ? ' : ' . $name : '') . ($page ? ' - ' . $page : ''))
            : '-';
    }
}
