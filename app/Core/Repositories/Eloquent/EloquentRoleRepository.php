<?php

namespace App\Core\Repositories\Eloquent;

use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Repositories\Contracts\RoleRepositoryInterface;
use App\Core\Support\AdminRouteResolver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\PermissionRegistrar;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private readonly ?AdminRouteResolver $adminRoutes = null,
    ) {}

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
            ->map(fn (Role $role) => $this->mapDatatableRow($role))
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

    public function create(string $moduleId, array $data): Role
    {
        $role = Role::query()->create([
            'module_id' => $moduleId,
            'name' => trim((string) $data['name']),
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role->refresh();
    }

    public function update(Role $role, array $data): Role
    {
        $role->update([
            'name' => trim((string) $data['name']),
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role->refresh();
    }

    public function syncPermissions(Role $role, string $moduleId, array $permissionIds): void
    {
        if (empty($permissionIds)) {
            $role->syncPermissions([]);
        } else {
            $perms = Permission::query()
                ->where('module_id', $moduleId)
                ->whereIn('id', $permissionIds)
                ->where('guard_name', 'web')
                ->whereNull('deleted_at')
                ->get();

            $role->syncPermissions($perms);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function delete(Role $role): void
    {
        $role->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function findByIdWithTrashed(string $moduleId, string $id): ?Role
    {
        return Role::withTrashed()
            ->where('module_id', $moduleId)
            ->whereKey($id)
            ->first();
    }

    public function restore(Role $role): bool
    {
        if (! $role->trashed()) {
            return false;
        }

        $ok = (bool) $role->restore();

        if ($ok) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        return $ok;
    }

    private function buildBaseDatatableQuery(string $moduleId, string $archivedMode = 'active'): Builder
    {
        $q = Role::query()
            ->where('module_id', $moduleId)
            ->select(['id', 'name', 'guard_name', 'created_at', 'deleted_at'])
            ->with([
                'permissions' => fn ($query) => $query
                    ->where('permissions.module_id', $moduleId)
                    ->select('permissions.id', 'permissions.name', 'permissions.page'),
            ])
            ->withCount([
                'permissions as permissions_count' => fn ($query) => $query
                    ->where('permissions.module_id', $moduleId),
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
                    ->orWhereHas('permissions', function (Builder $pq) use ($moduleId, $search) {
                        $pq->where('permissions.module_id', $moduleId)
                            ->where(function (Builder $permissionSearch) use ($search) {
                                $permissionSearch
                                    ->where('permissions.name', 'like', "%{$search}%")
                                    ->orWhere('permissions.page', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            $q->where('name', 'like', "%{$name}%");
        }

        $permission = trim((string) ($filters['permission'] ?? ''));
        if ($permission !== '') {
            $q->whereHas('permissions', function (Builder $pq) use ($moduleId, $permission) {
                $pq->where('permissions.module_id', $moduleId)
                    ->where(function (Builder $permissionSearch) use ($permission) {
                        $permissionSearch
                            ->where('permissions.name', 'like', "%{$permission}%")
                            ->orWhere('permissions.page', 'like', "%{$permission}%");
                    });
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
            'permissions_count' => 'permissions_count',
            'created_at' => 'created_at',
        ];

        if ($sortField && isset($map[$sortField])) {
            return $q->orderBy($map[$sortField], $sortDir);
        }

        return $q->orderByDesc('created_at');
    }

    private function mapDatatableRow(Role $role): array
    {
        $adminRoutes = $this->adminRoutes ?? app(AdminRouteResolver::class);
        $permissions = $role->permissions
            ->map(function (Permission $perm) {
                return [
                    'id' => (string) $perm->id,
                    'page' => (string) ($perm->page ?: 'Uncategorized'),
                    'name' => (string) $perm->name,
                ];
            })
            ->sortBy([
                ['page', 'asc'],
                ['name', 'asc'],
            ])
            ->values();

        $byPage = $permissions
            ->groupBy(fn (array $perm) => $perm['page'])
            ->map(fn ($items, $page) => $page . ' (' . $items->count() . ')')
            ->values();

        $isArchived = $role->deleted_at !== null;

        return [
            'id' => (string) $role->id,
            'name' => (string) $role->name,
            'guard_name' => (string) ($role->guard_name ?? 'web'),
            'created_at' => $role->created_at?->toDateTimeString(),
            'created_at_text' => $role->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $role->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $role->deleted_at?->format('M d, Y h:i A') ?? null,
            'is_archived' => $isArchived,
            'permissions_count' => (int) $role->permissions_count,
            'permissions_preview' => $byPage->take(2)->implode(', '),
            'permissions_more_count' => max(0, $byPage->count() - 2),
            'permission_ids' => $permissions->pluck('id')->values()->all(),
            'permissions' => $permissions->all(),
            'update_url' => $isArchived ? null : $adminRoutes->route('access.roles.update', $role),
            'delete_url' => $isArchived ? null : $adminRoutes->route('access.roles.destroy', $role),
            'restore_url' => $isArchived ? $adminRoutes->route('access.roles.restore', $role->id) : null,
        ];
    }
}
