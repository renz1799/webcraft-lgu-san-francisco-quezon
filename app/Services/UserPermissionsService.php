<?php

namespace App\Services;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Contracts\UserPermissionsServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserPermissionsService implements UserPermissionsServiceInterface
{
    public function indexData(): array
    {
        // Eager-load roles to avoid N+1
        $users = User::with('roles')->where('user_type', '!=', 'Administrator')->get();

        $permissions = Permission::all()->groupBy(function ($p) {
            // Expects names like "view Users", "edit Users"
            return explode(' ', $p->name, 2)[1] ?? 'others';
        });

        return compact('users', 'permissions');
    }

    public function getUserPermissions(User $user): array
    {
        $this->ensureDefaultRole($user);

        $permissions = Permission::all()->groupBy(function ($p) {
            return explode(' ', $p->name, 2)[1] ?? 'others';
        });

        $userPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
        $roles           = Role::pluck('name')->toArray();
        $currentRole     = $user->roles()->pluck('name')->first();

        return [
            'permissions'     => $permissions,
            'userPermissions' => $userPermissions,
            'roles'           => $roles,
            'currentRole'     => $currentRole,
        ];
    }

    public function updateUserRoleAndPermissions(User $user, ?string $roleName, array $permissionNames): void
    {
        DB::transaction(function () use ($user, $roleName, $permissionNames) {
            $currentRole = $user->roles()->pluck('name')->first();

            if ($roleName && $roleName !== $currentRole) {
                // Role changed → reset roles + set role defaults
                $user->syncRoles([]);
                $newRole = Role::where('name', $roleName)->firstOrFail();
                $user->assignRole($newRole);

                $defaults = $newRole->permissions;
                $user->syncPermissions($defaults);

                Log::info('Role updated; default permissions applied', [
                    'user_id' => $user->id,
                    'role'    => $roleName,
                    'perms'   => $defaults->pluck('name')->toArray(),
                ]);
            } else {
                // Only custom permissions changed
                $permissionObjects = Permission::whereIn('name', $permissionNames)->get();
                $user->syncPermissions($permissionObjects);

                Log::info('Custom permissions updated', [
                    'user_id' => $user->id,
                    'perms'   => $permissionObjects->pluck('name')->toArray(),
                ]);
            }
        });
    }

    public function ensureDefaultRole(User $user, string $defaultRole = 'User'): void
    {
        if ($user->roles()->doesntExist()) {
            $role = Role::firstOrCreate(['name' => $defaultRole, 'guard_name' => 'web']);
            $user->assignRole($role);
            Log::info('Default role assigned', ['user_id' => $user->id, 'role' => $defaultRole]);
        }
    }

    public function deleteUser(User $user): void
    {
        $snapshot = $user->toArray();
        $user->delete();

        Log::info('User deleted', [
            'deleted_by' => auth()->id(),
            'user_id'    => $snapshot['id'] ?? null,
        ]);
    }

    public function updateStatus(User $user, bool $isActive): void
    {
        $user->is_active = $isActive;
        $user->save();

        Log::info('User status updated', [
            'user_id'   => $user->id,
            'is_active' => $isActive,
        ]);
    }
}
