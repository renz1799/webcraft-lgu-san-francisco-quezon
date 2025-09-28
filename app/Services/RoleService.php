<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\Contracts\RoleServiceInterface;
use App\Services\Contracts\AuditLogServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class RoleService implements RoleServiceInterface
{
    public function __construct(
        private readonly RoleRepositoryInterface $repo,
        private readonly AuditLogServiceInterface $audit,
    ) {}

    public function indexData(): array
    {
        return [
            'roles'       => $this->repo->allWithPermissions(),
            'permissions' => Permission::all(),
        ];
    }

    public function paginateWithPermissions(int $perPage = 30): LengthAwarePaginator
    {
        return $this->repo->paginateWithPermissions($perPage);
    }

    public function create(array $data): Role
    {
        $role = $this->repo->create([
            'name'       => $data['name'],
            'guard_name' => 'web',
        ]);

        $permIds = array_values($data['permissions'] ?? []);
        $this->repo->syncPermissions($role, $permIds);

        $this->audit->record(
            'role.created',
            $role,
            [],
            [
                'name'        => $role->name,
                'guard_name'  => $role->guard_name,
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
            'name'        => $role->name,
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        $role = $this->repo->update($role, ['name' => $data['name']]);

        $permIds = array_values($data['permissions'] ?? []);
        $this->repo->syncPermissions($role, $permIds);

        $after = [
            'name'        => $role->name,
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
            'id'          => $role->id,
            'name'        => $role->name,
            'permissions' => $role->permissions()->pluck('name')->values()->all(),
        ];

        $this->repo->delete($role);

        $this->audit->record(
            'role.deleted',
            $role,          // soft-deleted subject
            $snapshot,
            ['deleted_at' => now()->toDateTimeString()],
            [
                'ip' => request()->ip(),
                'ua' => request()->userAgent(),
            ]
        );
    }
}
