<?php
// app/Repositories/Eloquent/EloquentAuditLogRepository.php

namespace App\Core\Repositories\Eloquent;

use App\Core\Builders\Contracts\AuditLogs\AuditLogDatatableRowBuilderInterface;
use App\Core\Models\AuditLog;
use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Core\Support\AdminRouteResolver;
use App\Core\Support\CurrentContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class EloquentAuditLogRepository implements AuditLogRepositoryInterface
{
    public function __construct(
        private readonly AuditLogDatatableRowBuilderInterface $auditLogDatatableRowBuilder,
        private readonly ?CurrentContext $context = null,
        private readonly ?AdminRouteResolver $adminRoutes = null,
    ) {}

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
            ->map(fn (AuditLog $log) => $this->auditLogDatatableRowBuilder->build($log))
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
            'module:id,code,name',
            'actor' => fn ($qq) => $qq->withTrashed()
                ->select('id', 'username', 'email', 'deleted_at')
                ->with(['profile:id,user_id,first_name,middle_name,last_name,name_extension']),
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
        return $this->applyFilters(
            $this->buildBaseQuery($withRelations),
            $filters,
        );
    }

    private function applyFilters(Builder $q, array $filters): Builder
    {
        $forcedModuleId = $this->forcedModuleId();
        $module = trim((string) ($filters['module'] ?? $filters['module_name'] ?? ''));

        if ($forcedModuleId !== null) {
            $q->where('module_id', $forcedModuleId);
        } elseif ($module !== '') {
            $this->applyModuleFilter($q, $module);
        }

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

        $subjectType = $this->normalizeSubjectTypeFilter($filters['subject_type'] ?? null);
        if ($subjectType !== null) {
            $q->where('subject_type', $subjectType);
        }

        return $q;
    }

    public function findForPrint(array $filters): Collection
    {
        return $this->buildFilteredQuery($filters)->get();
    }

    private function applyModuleFilter(Builder $q, string $module): void
    {
        $q->whereHas('module', function (Builder $moduleQuery) use ($module) {
            $moduleQuery->whereKey($module)
                ->orWhere('name', 'like', '%' . $module . '%')
                ->orWhere('code', 'like', '%' . $module . '%');
        });
    }

    private function normalizeSubjectTypeFilter(mixed $subjectTypeInput): ?string
    {
        $subjectType = trim((string) $subjectTypeInput);

        if ($subjectType === '') {
            return null;
        }

        return match (strtolower($subjectType)) {
            'user' => User::class,
            'permission' => Permission::class,
            'role' => Role::class,
            default => in_array($subjectType, [User::class, Permission::class, Role::class], true)
                ? $subjectType
                : null,
        };
    }

    private function forcedModuleId(): ?string
    {
        $adminRoutes = $this->adminRoutes ?? app(AdminRouteResolver::class);

        if (! $adminRoutes->isModuleScoped()) {
            return null;
        }

        return ($this->context ?? app(CurrentContext::class))->moduleId();
    }
}
