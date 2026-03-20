<?php

namespace App\Repositories\Eloquent;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class EloquentPermissionRepository implements PermissionRepositoryInterface
{
    public function datatable(string $moduleId, array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));
        $archivedMode = $this->resolveArchivedMode($filters);

        $recordsTotal = (clone $this->buildBaseDatatableQuery($moduleId, $archivedMode))->count();

        $filteredForCount = $this->buildFilteredDatatableQuery($moduleId, $filters);
        $recordsFiltered = (clone $filteredForCount)->count();

        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = $this->applyDatatableSort(
            $this->buildFilteredDatatableQuery($moduleId, $filters),
            $filters
        )
            ->forPage($page, $size)
            ->get()
            ->map(fn (Permission $permission) => $this->mapDatatableRow($permission))
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

    public function findByIdWithTrashed(string $moduleId, string $id): ?Permission
    {
        return Permission::withTrashed()
            ->where('module_id', $moduleId)
            ->whereKey($id)
            ->first();
    }

    public function create(string $moduleId, array $data): Permission
    {
        return Permission::create([
            ...$data,
            'module_id' => $moduleId,
        ]);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);

        return $permission->refresh();
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }

    public function restore(Permission $permission): bool
    {
        if (! $permission->trashed()) {
            return false;
        }

        return (bool) $permission->restore();
    }

    private function buildBaseDatatableQuery(string $moduleId, string $archivedMode = 'active'): Builder
    {
        $q = Permission::query()
            ->where('module_id', $moduleId)
            ->select(['id', 'name', 'page', 'guard_name', 'created_at', 'deleted_at'])
            ->with([
                'roles' => fn ($query) => $query
                    ->where('roles.module_id', $moduleId)
                    ->select('roles.id', 'roles.name'),
            ])
            ->withCount([
                'roles as roles_count' => fn ($query) => $query
                    ->where('roles.module_id', $moduleId),
            ]);

        if ($archivedMode === 'all') {
            $q->withTrashed();
        } elseif ($archivedMode === 'archived') {
            $q->onlyTrashed();
        }

        return $q;
    }

    private function buildFilteredDatatableQuery(string $moduleId, array $filters): Builder
    {
        $q = $this->buildBaseDatatableQuery($moduleId, $this->resolveArchivedMode($filters));

        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        if ($search !== '') {
            $q->where(function (Builder $sub) use ($moduleId, $search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('page', 'like', "%{$search}%")
                    ->orWhere('guard_name', 'like', "%{$search}%")
                    ->orWhereHas('roles', function (Builder $rq) use ($moduleId, $search) {
                        $rq->where('roles.module_id', $moduleId)
                            ->where('roles.name', 'like', "%{$search}%");
                    });
            });
        }

        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            $q->where('name', 'like', "%{$name}%");
        }

        $page = trim((string) ($filters['module'] ?? ''));
        if ($page !== '') {
            $q->where('page', 'like', "%{$page}%");
        }

        $guard = trim((string) ($filters['guard_name'] ?? ''));
        if ($guard !== '') {
            $q->where('guard_name', $guard);
        }

        $role = trim((string) ($filters['role'] ?? ''));
        if ($role !== '') {
            $q->whereHas('roles', function (Builder $rq) use ($moduleId, $role) {
                $rq->where('roles.module_id', $moduleId)
                    ->where('roles.name', 'like', "%{$role}%");
            });
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            try {
                $from = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
                $q->where('created_at', '>=', $from);
            } catch (\Throwable $_e) {
                // ignored after request validation
            }
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            try {
                $to = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
                $q->where('created_at', '<=', $to);
            } catch (\Throwable $_e) {
                // ignored after request validation
            }
        }

        return $q;
    }

    private function resolveArchivedMode(array $filters): string
    {
        $mode = trim((string) ($filters['archived'] ?? 'active'));

        if (in_array($mode, ['active', 'archived', 'all'], true)) {
            return $mode;
        }

        return 'active';
    }

    private function applyDatatableSort(Builder $q, array $filters): Builder
    {
        $sortField = $filters['sorters'][0]['field'] ?? null;
        $sortDir = (($filters['sorters'][0]['dir'] ?? 'desc') === 'asc') ? 'asc' : 'desc';

        $map = [
            'name' => 'name',
            'page' => 'page',
            'guard_name' => 'guard_name',
            'roles_count' => 'roles_count',
            'created_at' => 'created_at',
        ];

        if ($sortField && isset($map[$sortField])) {
            return $q->orderBy($map[$sortField], $sortDir);
        }

        return $q
            ->orderBy('page')
            ->orderBy('name');
    }

    private function mapDatatableRow(Permission $permission): array
    {
        $roles = $permission->roles
            ->pluck('name')
            ->filter()
            ->sort()
            ->values();

        $isArchived = $permission->deleted_at !== null;

        return [
            'id' => (string) $permission->id,
            'name' => (string) $permission->name,
            'page' => (string) ($permission->page ?: 'Uncategorized'),
            'guard_name' => (string) ($permission->guard_name ?: 'web'),
            'roles_count' => (int) $permission->roles_count,
            'roles_preview' => $roles->take(2)->implode(', '),
            'roles_more_count' => max(0, $roles->count() - 2),
            'created_at' => $permission->created_at?->toDateTimeString(),
            'created_at_text' => $permission->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $permission->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $permission->deleted_at?->format('M d, Y h:i A') ?? null,
            'is_archived' => $isArchived,
            'update_url' => $isArchived ? null : route('access.permissions.update', $permission),
            'delete_url' => $isArchived ? null : route('access.permissions.destroy', $permission),
            'restore_url' => $isArchived ? route('access.permissions.restore', $permission->id) : null,
        ];
    }
}
