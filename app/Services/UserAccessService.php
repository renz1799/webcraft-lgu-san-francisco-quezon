<?php

namespace App\Services;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Contracts\UserAccessServiceInterface;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class UserAccessService implements UserAccessServiceInterface
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

        public function getEditData(User $user): array
    {
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('page'); // you already have a 'page' column

        $userRole = $user->roles()->first(); // current role

        // Group user’s direct permissions by page -> actions array (for the form)
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
         * Example $nested: ["Users" => ["Users" => ["view","modify"]], ...]
         */
        public function syncNestedPermissions(User $user, array $nested, ?string $roleName = null): int
        {
            return DB::transaction(function () use ($user, $nested, $roleName) {
                // 0) Role reset (optional)
                if ($roleName) {
                    $user->syncRoles([]);
                    $role = Role::where('name', $roleName)->firstOrFail();
                    $user->assignRole($role);
                    $user->syncPermissions($role->permissions);

                    Log::info('perm.update: role reset to defaults', [
                        'user_id' => $user->id,
                        'role'    => $roleName,
                        'defaults'=> $role->permissions()
                                        ->pluck('name', 'id')
                                        ->take(20), // avoid log spam
                    ]);
                }

                // 1) No nested payload → clear directs
                if (empty($nested)) {
                    $user->syncPermissions([]);
                    app(PermissionRegistrar::class)->forgetCachedPermissions();
                    Log::info('perm.update: cleared all direct permissions', ['user_id' => $user->id]);
                    return 0;
                }

                // 2) Build dictionary [page][resource][action] => id (case-insensitive + aliases)
                $pagesRequested = array_keys($nested);
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

                // 4) Apply + log diagnostics
                $user->syncPermissions($ids);
                app(PermissionRegistrar::class)->forgetCachedPermissions();

                Log::info('perm.update: mapping result', [
                    'user_id'      => $user->id,
                    'apply_count'  => count($ids),
                    'found_sample' => $found,
                    'miss_sample'  => $misses,
                ]);

                // Optional: show a quick diff vs existing directs (handy if still zero)
                // $current = $user->permissions()->pluck('id')->toArray();
                // Log::debug('perm.update: current direct ids', ['ids' => $current]);

                return count($ids);
            });
        }

        /** Normalize action names so UI "modify" maps to DB "edit", etc. */
        private function normalizeAction(string $action): string
        {
            $key = strtolower(trim($action));
            // add/adjust to match your DB:
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

        public function resetPasswordToTemporary(User $user): string
        {
            $temp = $this->generateAlphaNum(8); // 8-char, A–Z/a–z/0–9 (no ambiguous)

            $user->forceFill([
                'password' => Hash::make($temp),
                // 'must_change_password' => true, // optional flag if you add the column
            ])->save();

            Log::info('password.reset: admin initiated temp password', [
                'user_id' => $user->id,
                'by'      => auth()->id(),
            ]);

            return $temp; // do NOT log this
        }

        /**
         * Generate a cryptographically secure alphanumeric string.
         * Ensures at least one uppercase, one lowercase, and one digit.
         * Excludes ambiguous chars: 0, O, 1, l, I.
         */
        private function generateAlphaNum(int $length = 8): string
        {
            $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            $lower   = 'abcdefghijkmnopqrstuvwxyz';
            $digits  = '23456789';
            $alphabet = $upper . $lower . $digits;

            $max = strlen($alphabet) - 1;

            // start with a random string
            $buf = '';
            for ($i = 0; $i < $length; $i++) {
                $buf .= $alphabet[random_int(0, $max)];
            }

            // enforce class presence by overwriting random positions if needed
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
