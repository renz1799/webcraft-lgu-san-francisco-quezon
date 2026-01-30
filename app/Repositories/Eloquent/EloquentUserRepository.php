<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\Role; // your App\Models\Role extends Spatie Role
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class EloquentUserRepository implements UserRepositoryInterface
{
    /** Create a user */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /** Update a user */
    public function update(User $user, array $data): User
    {
        $user->fill($data)->save();
        return $user->refresh();
    }

    /** Find by id (active only) */
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    /** Find by id incl. soft-deleted */
    public function findByIdWithTrashed(string $id): ?User
    {
        return User::withTrashed()->find($id);
    }

    /** Find by email */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /** List users (simple paginate) */
    public function paginate(int $perPage = 30): LengthAwarePaginator
    {
        return User::paginate($perPage);
    }

    /** Soft-delete */
    public function delete(User $user): void
    {
        $user->delete();
    }

    /** Restore soft-deleted */
    public function restore(User $user): bool
    {
        // restore() returns int/bool; cast to bool for interface
        return (bool) $user->restore();
    }

    /** Permanently delete */
    public function forceDelete(User $user): bool
    {
        return (bool) $user->forceDelete();
    }

    /** Convenience: restore by id */
    public function restoreById(string $id): bool
    {
        $user = User::withTrashed()->find($id);
        return $user ? (bool) $user->restore() : false;
    }

    /** Convenience: force delete by id */
    public function forceDeleteById(string $id): bool
    {
        $user = User::withTrashed()->find($id);
        return $user ? (bool) $user->forceDelete() : false;
    }

    /**
     * Assign a role (resolve by UUID or name) and sync default role permissions as directs.
     * NOTE: Spatie already grants role permissions via Gate; syncing as directs keeps UI “toggles” in sync.
     */
    public function assignRoleAndSyncPermissions(User $user, string $roleInput): void
    {
        // clear cached permissions to avoid stale reads
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Str::isUuid($roleInput)
            ? Role::findById($roleInput, 'web')
            : Role::findByName($roleInput, 'web');

        // assign by name to avoid id key-type surprises
        $user->syncRoles([]);           // avoid mixed roles unless you support multi-role
        $user->assignRole($role->name);

        // apply role defaults as direct permissions (optional but matches your UI)
        if ($role->permissions->isNotEmpty()) {
            $user->syncPermissions($role->permissions);
        } else {
            // if role has no defaults, clear directs to reflect “role only”
            $user->syncPermissions([]);
        }

        // refresh cache after changes
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function paginateForPermissionsTable(?string $q, int $page, int $size): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['roles:id,name'])
            ->select(['id','username','email','is_active','created_at'])
            ->latest('created_at');

        if ($q) {
            $query->where(function ($qq) use ($q) {
                $qq->where('username', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%");
            });
        }

        return $query->paginate($size, ['*'], 'page', $page);
    }

        public function listForTaskReassign(): array
    {
        return User::query()
            ->with(['profile:id,user_id,first_name,middle_name,last_name,name_extension'])
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('username')
            ->get(['id', 'username'])
            ->map(function (User $u) {
                $name = $u->profile?->full_name ?: ($u->username ?: 'Unknown User');
                return [
                    'id' => (string) $u->id,
                    'name' => trim((string) $name),
                ];
            })
            ->values()
            ->all();
    }

        public function getUserIdsByRoles(array $roleNames): array
    {
        return User::role($roleNames)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->values()
            ->all();
    }
}

