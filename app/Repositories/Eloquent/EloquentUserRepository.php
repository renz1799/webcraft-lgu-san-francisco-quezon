<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Spatie\Permission\Models\Role;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function assignRoleAndSyncPermissions(User $user, string $roleName): void
    {
        // with Spatie, guard must match (usually 'web')
        $role = Role::findByName($roleName, 'web');

        $user->assignRole($role);
        $permissions = $role->permissions;
        if ($permissions->isNotEmpty()) {
            $user->syncPermissions($permissions);
        }
    }
}
