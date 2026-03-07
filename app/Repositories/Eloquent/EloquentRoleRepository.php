<?php

namespace App\Repositories\Eloquent;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\PermissionRegistrar;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));
        $archivedMode = $this->resolveArchivedMode($filters);

        $recordsTotal = (clone $this->buildBaseDatatableQuery($archivedMode))->count();

        $filteredForCount = $this->buildFilteredDatatableQuery($filters);
        $recordsFiltered = (clone $filteredForCount)->count();

        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = $this->applyDatatableSort(
            $this->buildFilteredDatatableQuery($filters),
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

    public function create(array $data): Role
    {
        $role = Role::create([
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

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        if (empty($permissionIds)) {
            $role->syncPermissions([]);
        } else {
            $perms = Permission::query()
                ->whereIn('id', $permissionIds)
                ->where('guard_name', 'web')
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

    public function findByIdWithTrashed(string $id): ?Role
    {
        return Role::withTrashed()->find($id);
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

    private function buildBaseDatatableQuery(string $archivedMode = 'active'): Builder
    {
        $q = Role::query()
            ->with(['permissions:id,name,page'])
            ->withCount('permissions')
            ->select(['id', 'name', 'guard_name', 'created_at', 'deleted_at']);

        if ($archivedMode === 'all') {
            $q->withTrashed();
        } elseif ($archivedMode === 'archived') {
            $q->onlyTrashed();
        }

        return $q;
    }

    private function buildFilteredDatatableQuery(array $filters): Builder
    {
        $q = $this->buildBaseDatatableQuery($this->resolveArchivedMode($filters));

        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        if ($search !== '') {
            $q->where(function (Builder $sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhereHas('permissions', function (Builder $pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('page', 'like', "%{$search}%");
                    });
            });
        }

        $name = trim((string) ($filters['name'] ?? ''));
        if ($name !== '') {
            $q->where('name', 'like', "%{$name}%");
        }

        $permission = trim((string) ($filters['permission'] ?? ''));
        if ($permission !== '') {
            $q->whereHas('permissions', function (Builder $pq) use ($permission) {
                $pq->where('name', 'like', "%{$permission}%")
                    ->orWhere('page', 'like', "%{$permission}%");
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
            'update_url' => $isArchived ? null : route('access.roles.update', $role),
            'delete_url' => $isArchived ? null : route('access.roles.destroy', $role),
            'restore_url' => $isArchived ? route('access.roles.restore', $role->id) : null,
        ];
    }
}
