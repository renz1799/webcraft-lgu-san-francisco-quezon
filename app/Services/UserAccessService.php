<?php

namespace App\Services;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Contracts\UserAccessServiceInterface;
use App\Services\Contracts\AuditLogServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class UserAccessService implements UserAccessServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users, 
        private readonly AuditLogServiceInterface $audit
    ) {}

    /* ----------------------------- Queries ------------------------------ */

    public function indexData(?string $q = null): array
    {
        $q = $q ? trim($q) : null;
        if ($q === '') $q = null;

        $query = User::query()
            ->with(['roles', 'profile'])
            ->where('user_type', '!=', 'Administrator');

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('email', 'like', "%{$q}%")
                    ->orWhere('username', 'like', "%{$q}%")
                    // optional: search full name parts
                    ->orWhereHas('profile', function ($p) use ($q) {
                        $p->where('first_name', 'like', "%{$q}%")
                        ->orWhere('middle_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('name_extension', 'like', "%{$q}%");
                    });
            });
        }

        $users = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(function ($p) {
                return explode(' ', $p->name, 2)[1] ?? 'others';
            });

        return compact('users', 'permissions', 'q');
    }

    public function paginateForPermissionsTable(?string $q, int $page, int $size): LengthAwarePaginator
    {
        return $this->users->paginateForPermissionsTable($q, $page, $size);
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

    /* ----------------------------- Mutations ---------------------------- */

    public function updateUserRoleAndPermissions(User $user, ?string $roleName, array $permissionNames): void
    {
        DB::transaction(function () use ($user, $roleName, $permissionNames) {
            $beforeRole  = $user->roles()->pluck('name')->first();
            $beforePerms = $user->permissions()->pluck('name')->values()->all();

            if ($roleName && $roleName !== $beforeRole) {
                // Role changed → reset roles + set role defaults
                $user->syncRoles([]);
                $newRole = Role::where('name', $roleName)->firstOrFail();
                $user->assignRole($newRole);

                $defaults = $newRole->permissions;
                $user->syncPermissions($defaults);

                // Audit: role changed
                $this->audit->record(
                    'user.role.changed',
                    $user,
                    ['role' => $beforeRole],
                    ['role' => $roleName],
                    $this->meta(),
                    null
                );

                Log::info('Role updated; default permissions applied', [
                    'user_id' => $user->id,
                    'role'    => $roleName,
                    'perms'   => $defaults->pluck('name')->toArray(),
                ]);
            } else {
                // Only custom permissions changed
                $permissionObjects = Permission::whereIn('name', $permissionNames)->get();
                $user->syncPermissions($permissionObjects);

                // Audit: direct permissions synced
                $afterPerms = $user->permissions()->pluck('name')->values()->all();
                $this->audit->record(
                    'user.permissions.synced',
                    $user,
                    ['direct_permissions' => $beforePerms],
                    ['direct_permissions' => $afterPerms],
                    $this->meta(),
                    null
                );

                Log::info('Custom permissions updated', [
                    'user_id' => $user->id,
                    'perms'   => $afterPerms,
                ]);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }

    public function ensureDefaultRole(User $user, string $defaultRole = 'User'): void
    {
        if ($user->roles()->doesntExist()) {
            $role = Role::firstOrCreate(['name' => $defaultRole, 'guard_name' => 'web']);
            $user->assignRole($role);

            $this->audit->record(
                'user.role.assigned_default',
                $user,
                ['role' => null],
                ['role' => $defaultRole],
                $this->meta(),
                null
            );

            Log::info('Default role assigned', ['user_id' => $user->id, 'role' => $defaultRole]);
        }
    }

    /** Soft delete the user (via repository). */
    public function deleteUser(User $user): void
    {
        $snapshot = $user->only(['id', 'username', 'email', 'user_type', 'is_active']);

        $this->users->delete($user); // <— repo does soft delete

        $this->audit->record(
            'user.deleted',
            $user,
            $snapshot,
            ['deleted_at' => now()->toDateTimeString()],
            $this->meta(),
            null
        );

        Log::info('User soft-deleted', [
            'deleted_by' => auth()->id(),
            'user_id'    => $user->id,
        ]);
    }

    /** Restore a soft-deleted user (returns true if restored). */
    public function restoreUser(string|User $user): bool
    {
        $model = $user instanceof User ? $user : $this->users->findByIdWithTrashed($user);
        if (! $model) {
            return false;
        }

        $ok = $this->users->restore($model);

        if ($ok) {
            $this->audit->record(
                'user.restored',
                $model,
                ['deleted_at' => $model->deleted_at?->toDateTimeString()],
                ['restored_at' => now()->toDateTimeString()],
                $this->meta(),
                null
            );
        }

        return $ok;
    }

    /** Permanently delete a user (returns true if hard-deleted). */
    public function forceDeleteUser(string|User $user): bool
    {
        $model = $user instanceof User ? $user : $this->users->findByIdWithTrashed($user);
        if (! $model) {
            return false;
        }

        $snapshot = $model->only(['id', 'username', 'email', 'user_type', 'is_active']);
        $ok = $this->users->forceDelete($model);

        if ($ok) {
            $this->audit->record(
                'user.force_deleted',
                $model, // still valid as an in-memory subject
                $snapshot,
                [],
                $this->meta(),
                'permanent removal'
            );
        }

        return $ok;
    }
    public function updateStatus(User $user, bool $isActive): void
    {
        $before = (bool) $user->is_active;
        $user->is_active = $isActive;
        $user->save();

        $this->audit->record(
            'user.status.updated',
            $user,
            ['is_active' => $before],
            ['is_active' => $isActive],
            $this->meta(),
            null
        );

        Log::info('User status updated', [
            'user_id'   => $user->id,
            'is_active' => $isActive,
        ]);
    }

    public function getEditData(User $user): array
    {
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('page');

        $userRole = $user->roles()->first();

        $userPermissions = $user->permissions->groupBy('page')->map(function ($perPage) {
            return $perPage->mapToGroups(function ($permission) {
                $parts = explode(' ', $permission->name);
                $action = strtolower(array_shift($parts));  // "view", "modify", ...
                $resource = implode(' ', $parts);
                return [$resource => [$action]];
            })->map(fn ($actions) => $actions->flatten()->unique()->values()->all());
        });

        return compact('user', 'roles', 'permissions', 'userPermissions', 'userRole');
    }

    /**
     * Accepts the nested payload and optional role name.
     * Example $nested: ["Users" => ["User Lists" => ["view","modify"]], ...]
     */
    public function syncNestedPermissions(User $user, array $nested, ?string $roleName = null): int
    {
        $actor = auth()->user();
        if (! $actor || ! $actor->hasRole('Administrator')) {
            abort(403, 'Only administrators may manage user roles and permissions.');
        }

        return DB::transaction(function () use ($user, $nested, $roleName) {
            // Snapshot before
            $beforeRole  = $user->roles()->pluck('name')->first();
            $beforePerms = $user->permissions()->pluck('name')->values()->all();

            // 0) Role reset (optional)
            if ($roleName) {
                $user->syncRoles([]);
                $role = Role::where('name', $roleName)->firstOrFail();
                $user->assignRole($role);
                $user->syncPermissions($role->permissions);

                $this->audit->record(
                    'user.role.changed',
                    $user,
                    ['role' => $beforeRole],
                    ['role' => $roleName],
                    $this->meta(),
                    'via nested permissions sync'
                );

                Log::info('perm.update: role reset to defaults', [
                    'user_id'  => $user->id,
                    'role'     => $roleName,
                    'defaults' => $role->permissions()->pluck('name', 'id')->take(20),
                ]);
            }

            // 1) No nested payload → clear directs
            if (empty($nested)) {
                $user->syncPermissions([]);
                app(PermissionRegistrar::class)->forgetCachedPermissions();

                $this->audit->record(
                    'user.permissions.synced',
                    $user,
                    ['direct_permissions' => $beforePerms],
                    ['direct_permissions' => []],
                    $this->meta(),
                    'cleared all direct permissions'
                );

                Log::info('perm.update: cleared all direct permissions', ['user_id' => $user->id]);
                return 0;
            }

            // 2) Build dictionary [page][resource][action] => id
            $pagesRequested = array_slice(array_keys($nested), 0, 50);
            $pool = Permission::whereIn('page', $pagesRequested)->get();

            Log::info('perm.update: pool summary', [
                'user_id'      => $user->id,
                'pages_req'    => $pagesRequested,
                'pool_count'   => $pool->count(),
                'sample_names' => $pool->pluck('name')->take(20),
            ]);

            $dict = [];
            foreach ($pool as $p) {
                [$actionRaw, $resourceRaw] = explode(' ', $p->name, 2);
                $page     = strtolower(trim((string) $p->page));
                $action   = $this->normalizeAction($actionRaw ?? '');
                $resource = strtolower(trim($resourceRaw ?? ''));
                $dict[$page][$resource][$action] = $p->id;
            }

            // 3) Resolve incoming nested → ids
            $ids     = [];
            $found   = [];
            $misses  = [];

            foreach ($nested as $page => $resources) {
                $pKey = strtolower(trim($page));
                foreach ($resources as $resource => $actions) {
                    $rKey = strtolower(trim($resource));
                    foreach ((array) $actions as $action) {
                        $aKey = $this->normalizeAction($action);
                        $id   = $dict[$pKey][$rKey][$aKey] ?? null;

                        if ($id) {
                            $ids[] = $id;
                            if (count($found) < 25) {
                                $found[] = compact('pKey','rKey','aKey','id');
                            }
                        } else {
                            if (count($misses) < 25) {
                                $misses[] = compact('pKey','rKey','aKey');
                            }
                        }
                    }
                }
            }

            $ids = array_values(array_unique($ids));

            // 4) Apply + audit
            $user->syncPermissions($ids);
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $afterPerms = $user->permissions()->pluck('name')->values()->all();

            $this->audit->record(
                'user.permissions.synced',
                $user,
                ['direct_permissions' => $beforePerms],
                ['direct_permissions' => $afterPerms],
                [
                    ...$this->meta(),
                    'resolve_found_sample' => $found,
                    'resolve_miss_sample'  => $misses,
                ],
                'via nested permissions UI'
            );

            Log::info('perm.update: mapping result', [
                'user_id'      => $user->id,
                'apply_count'  => count($ids),
                'found_sample' => $found,
                'miss_sample'  => $misses,
            ]);

            return count($ids);
        });
    }


    /** Generate a cryptographically secure alphanumeric string (8 chars). */
    public function resetPasswordToTemporary(User $user): string
    {

        $temp = $this->generateAlphaNum(8);

        $user->forceFill([
            'password' => Hash::make($temp),
            'must_change_password' => true,
        ])->save();


        $this->audit->record(
            'user.password.reset',
            $user,
            [],
            ['reset' => true],
            $this->meta(),
            'temporary password generated by admin'
        );

        Log::info('password.reset: admin initiated temp password', [
            'user_id' => $user->id,
            'by'      => auth()->id(),
        ]);

        return $temp; // do NOT log or store the plaintext
    }

    /* ----------------------------- Helpers ------------------------------ */

    /** Normalize action names so UI "modify" maps to DB "edit", etc. */
    private function normalizeAction(string $action): string
    {
        $key = strtolower(trim($action));
        $aliases = [
            'modify' => 'edit',
            'edit'   => 'edit',
            'update' => 'update',
            'view'   => 'view',
            'show'   => 'view',
            'create' => 'create',
            'add'    => 'create',
            'delete' => 'delete',
            'remove' => 'delete',
            'export' => 'export',
        ];

        return $aliases[$key] ?? $key;
    }

    private function meta(): array
    {
        return [
            'ip' => request()->ip(),
            'ua' => request()->userAgent(),
        ];
    }

    /**
     * Generate secure, unambiguous alphanumeric string.
     * Ensures at least one uppercase, one lowercase, and one digit.
     */
    private function generateAlphaNum(int $length = 8): string
    {
        $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower   = 'abcdefghijkmnopqrstuvwxyz';
        $digits  = '23456789';
        $alphabet = $upper . $lower . $digits;

        $max = strlen($alphabet) - 1;

        $buf = '';
        for ($i = 0; $i < $length; $i++) {
            $buf .= $alphabet[random_int(0, $max)];
        }

        // enforce classes
        $len = $length - 1;
        if (!preg_match('/[A-Z]/', $buf)) {
            $buf[random_int(0, $len)] = $upper[random_int(0, strlen($upper) - 1)];
        }
        if (!preg_match('/[a-z]/', $buf)) {
            $buf[random_int(0, $len)] = $lower[random_int(0, strlen($lower) - 1)];
        }
        if (!preg_match('/\d/', $buf)) {
            $buf[random_int(0, $len)] = $digits[random_int(0, strlen($digits) - 1)];
        }

        return $buf;
    }
}
