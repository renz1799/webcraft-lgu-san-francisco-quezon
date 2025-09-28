<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Models\Permission;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function allWithPermissions(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function paginateWithPermissions(int $perPage = 30): LengthAwarePaginator
    {
        return Role::with('permissions')->paginate($perPage);
    }

    public function create(array $data): Role
    {
        // expects: name, guard_name (default web)
        $role = Role::create([
            'name'       => trim($data['name']),
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return $role->refresh();
    }

    public function update(Role $role, array $data): Role
    {
        $role->update([
            'name' => trim($data['name']),
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return $role->refresh();
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        if (empty($permissionIds)) {
            $role->syncPermissions([]); // clear
        } else {
            // Ensure only web-guard permissions
            $perms = Permission::whereIn('id', $permissionIds)
                ->where('guard_name', 'web')
                ->get();

            $role->syncPermissions($perms);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function delete(Role $role): void
    {
        $role->delete(); // soft delete (Role model should use SoftDeletes)
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
