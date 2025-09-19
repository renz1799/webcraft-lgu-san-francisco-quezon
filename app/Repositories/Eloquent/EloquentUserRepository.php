<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\Role; // <-- use YOUR model
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

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

    public function assignRoleAndSyncPermissions(User $user, string $roleInput): void
    {
        // clear stale cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $resolvedBy = Str::isUuid($roleInput) ? 'id' : 'name';
        $role = $resolvedBy === 'id'
            ? Role::findById($roleInput, 'web')     // <-- now returns App\Models\Role
            : Role::findByName($roleInput, 'web');

    /*     Log::info('Role resolved', [
            'user_id'       => $user->id,
            'input'         => $roleInput,
            'resolved_by'   => $resolvedBy,
            'role_id'       => (string) $role->id,
            'role_id_len'   => strlen((string) $role->id),
            'role_name'     => $role->name,
            'guard'         => $role->guard_name,
            'role_key_type' => $role->getKeyType(), // should be "string" now
            'role_class'    => get_class($role),    // should be App\Models\Role
        ]); */
 
        // assigning by name avoids ever passing a mis-cast id
        $user->assignRole($role->name);

        if ($role->permissions->isNotEmpty()) {
            $user->syncPermissions($role->permissions);
        }
    }
}
