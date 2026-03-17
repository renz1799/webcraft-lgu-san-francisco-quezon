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
use Illuminate\Support\Collection;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog
    {
        return AuditLog::create($data);
    }

    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));

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
                    User::class => fn (Builder $qq) => $qq->withTrashed()
                        ->select('id', 'username', 'email', 'deleted_at')
                        ->with(['profile:id,user_id,first_name,middle_name,last_name,name_extension']),
                ]);
            },
            'subject' => function (MorphTo $morph) {
                $morph->constrain([
                    User::class => fn (Builder $qq) => $qq->withTrashed()
                        ->select('id', 'username', 'email', 'deleted_at')
                        ->with(['profile:id,user_id,first_name,middle_name,last_name,name_extension']),
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
        $old = (array) ($log->changes_old ?? []);
        $new = (array) ($log->changes_new ?? []);

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

        $name = $subject->name
            ?? ($old['name'] ?? null)
            ?? ($new['name'] ?? null);

        $page = $subject->page
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

    public function findForPrint(array $filters): Collection
    {
        return AuditLog::query()
            ->with(['actor'])
            ->when($filters['date_from'] ?? null, function ($query, $value) {
                $query->whereDate('created_at', '>=', $value);
            })
            ->when($filters['date_to'] ?? null, function ($query, $value) {
                $query->whereDate('created_at', '<=', $value);
            })
            ->when($filters['module_name'] ?? null, function ($query, $value) {
                $query->where('module_name', $value);
            })
            ->when($filters['action'] ?? null, function ($query, $value) {
                $query->where('action', $value);
            })
            ->when($filters['actor_id'] ?? null, function ($query, $value) {
                $query->where('actor_id', $value);
            })
            ->when($filters['subject_type'] ?? null, function ($query, $value) {
                $query->where('subject_type', $value);
            })
            ->when($filters['search'] ?? null, function ($query, $value) {
                $query->where(function ($inner) use ($value) {
                    $inner->where('message', 'like', "%{$value}%")
                        ->orWhere('action', 'like', "%{$value}%");
                });
            })
            ->orderByDesc('created_at')
            ->get();
    }
}
