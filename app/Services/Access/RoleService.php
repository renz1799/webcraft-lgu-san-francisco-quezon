<?php

namespace App\Services\Access;

use App\Models\Permission;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\Contracts\AuditLogServiceInterface;
use App\Services\Contracts\RoleServiceInterface;

class RoleService implements RoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repo,
        private readonly AuditLogServiceInterface $audit,
    ) {}

    public function indexData(): array
    {
        return [
            'permissions' => Permission::query()
                ->orderBy('page')
                ->orderBy('name')
                ->get(),
        ];
    }

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));

        $filters = $params;
        unset($filters['page'], $filters['size']);

        return $this->repo->datatable($filters, $page, $size);
    }

    public function create(array $data): Role
    {
        $role = $this->repo->create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $permIds = array_values($data['permissions'] ?? []);
        $this->repo->syncPermissions($role, $permIds);

        $this->audit->record(
            'role.created',
            $role,
            [],
            [
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permissions' => Permission::whereIn('id', $permIds)->pluck('name')->values()->all(),
            ],
            [
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
            ]
        );

        return $role;
    }

    public function update(Role $role, array $data): Role
    {
        $before = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        $role = $this->repo->update($role, ['name' => $data['name']]);

        $permIds = array_values($data['permissions'] ?? []);
        $this->repo->syncPermissions($role, $permIds);

        $after = [
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        $this->audit->record(
            'role.updated',
            $role,
            $before,
            $after,
            [
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
            ]
        );

        return $role;
    }

    public function delete(Role $role): void
    {
        $snapshot = [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        $this->repo->delete($role);

        $this->audit->record(
            'role.deleted',
            $role,
            $snapshot,
            ['deleted_at' => now()->toDateTimeString()],
            [
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
            ]
        );
    }

    public function restoreRole(string|Role $role): bool
    {
        $model = $role instanceof Role ? $role : $this->repo->findByIdWithTrashed($role);

        if (! $model) {
            return false;
        }

        $deletedAt = $model->deleted_at?->toDateTimeString();
        $ok = $this->repo->restore($model);

        if (! $ok) {
            return false;
        }

        $model->refresh();

        $this->audit->record(
            'role.restored',
            $model,
            ['deleted_at' => $deletedAt],
            ['restored_at' => now()->toDateTimeString()],
            [
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
            ]
        );

        return true;
    }
}

