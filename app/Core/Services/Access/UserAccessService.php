<?php

namespace App\Core\Services\Access;

use App\Core\Models\ModelHasPermission;
use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Core\Services\Contracts\Access\UserAccessServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\AdminRouteResolver;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class UserAccessService implements UserAccessServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuditLogServiceInterface $audit,
        private readonly CurrentContext $context,
        private readonly ModuleRoleAssignmentServiceInterface $roleAssignments,
    ) {}

    /* ----------------------------- Queries ------------------------------ */

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));

        $filters = $params;
        unset($filters['page'], $filters['size']);

        return $this->users->datatable($filters, $page, $size);
    }

    public function getUserPermissions(User $user): array
    {
        $moduleId = $this->requireModuleId();
        $this->ensureUserBelongsToCurrentScope($user);
        $this->ensureDefaultRole($user);

        $permissions = Permission::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->groupBy(function (Permission $permission) {
                return explode(' ', $permission->name, 2)[1] ?? 'others';
            });

        $userPermissions = $this->currentModuleDirectPermissionNames($user);
        $roles = Role::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        return [
            'permissions' => $permissions,
            'userPermissions' => $userPermissions,
            'roles' => $roles,
            'currentRole' => $this->currentRoleName($user),
        ];
    }

    /* ----------------------------- Mutations ---------------------------- */

    public function updateUserRoleAndPermissions(User $user, ?string $roleName, array $permissionNames): void
    {
        $moduleId = $this->requireModuleId();
        $this->ensureUserBelongsToCurrentScope($user);

        DB::transaction(function () use ($user, $roleName, $permissionNames, $moduleId) {
            $beforeRole = $this->currentRoleName($user);
            $beforePerms = $this->currentModuleDirectPermissionNames($user);

            if ($roleName && $roleName !== $beforeRole) {
                $newRole = $this->resolveRoleForCurrentModule($moduleId, $roleName);
                $this->roleAssignments->sync($user, [$newRole]);
                $defaults = $this->permissionNamesForRole($newRole, $moduleId);

                $this->audit->record(
                    'user.role.changed',
                    $user,
                    ['role' => $beforeRole],
                    ['role' => $roleName],
                    $this->meta(),
                    null,
                    $this->buildRoleChangedDisplay($user, $beforeRole, $roleName)
                );

                Log::info('Role updated; default permissions applied', [
                    'user_id' => $user->id,
                    'role' => $roleName,
                    'perms' => $defaults,
                ]);

                return;
            }

            $permissionIds = $this->permissionIdsForCurrentModuleByNames($moduleId, $permissionNames);
            $this->syncDirectPermissionsForCurrentModule($user, $permissionIds, $moduleId);
            $afterPerms = $this->currentModuleDirectPermissionNames($user);

            $this->audit->record(
                'user.permissions.synced',
                $user,
                ['direct_permissions' => $beforePerms],
                ['direct_permissions' => $afterPerms],
                $this->meta(),
                null,
                $this->buildPermissionsSyncedDisplay($user, $beforePerms, $afterPerms)
            );

            Log::info('Custom permissions updated', [
                'user_id' => $user->id,
                'perms' => $afterPerms,
            ]);
        });
    }

    public function ensureDefaultRole(User $user, string $defaultRole = 'User'): void
    {
        $moduleId = $this->requireModuleId();
        $this->ensureUserBelongsToCurrentScope($user);

        if ($this->currentRoleName($user) !== null) {
            return;
        }

        $role = Role::query()->firstOrCreate([
            'module_id' => $moduleId,
            'name' => $defaultRole,
            'guard_name' => 'web',
        ]);

        $this->roleAssignments->assign($user, $role);

        $this->audit->record(
            'user.role.assigned_default',
            $user,
            ['role' => null],
            ['role' => $defaultRole],
            $this->meta(),
            null,
            $this->buildRoleAssignedDisplay($user, $defaultRole)
        );

        Log::info('Default role assigned', ['user_id' => $user->id, 'role' => $defaultRole]);
    }

    /** Soft delete the user (via repository). */
    public function deleteUser(User $user): void
    {
        $this->ensureUserBelongsToCurrentScope($user);
        $snapshot = $user->only(['id', 'username', 'email', 'user_type', 'is_active']);

        $this->users->delete($user);

        $this->audit->record(
            'user.deleted',
            $user,
            $snapshot,
            ['deleted_at' => now()->toDateTimeString()],
            $this->meta(),
            null,
            $this->buildUserArchivedDisplay($user, $snapshot)
        );

        Log::info('User soft-deleted', [
            'deleted_by' => auth()->id(),
            'user_id' => $user->id,
        ]);
    }

    /** Restore a soft-deleted user (returns true if restored). */
    public function restoreUser(string|User $user): bool
    {
        $model = $user instanceof User ? $user : $this->users->findByIdWithTrashed($user);
        if (! $model) {
            return false;
        }

        $this->ensureUserBelongsToCurrentScope($model);

        $ok = $this->users->restore($model);

        if ($ok) {
            $this->audit->record(
                'user.restored',
                $model,
                ['deleted_at' => $model->deleted_at?->toDateTimeString()],
                ['restored_at' => now()->toDateTimeString()],
                $this->meta(),
                null,
                $this->buildUserRestoredDisplay($model)
            );
        }

        return $ok;
    }

    public function updateStatus(User $user, bool $isActive): void
    {
        $this->ensureUserBelongsToCurrentScope($user);
        $before = (bool) $user->is_active;
        $user->is_active = $isActive;
        $user->save();

        $this->audit->record(
            'user.status.updated',
            $user,
            ['is_active' => $before],
            ['is_active' => $isActive],
            $this->meta(),
            null,
            $this->buildStatusUpdatedDisplay($user, $before, $isActive)
        );

        Log::info('User status updated', [
            'user_id' => $user->id,
            'is_active' => $isActive,
        ]);
    }

    public function getEditData(User $user): array
    {
        $moduleId = $this->requireModuleId();
        $this->ensureUserBelongsToCurrentScope($user);

        $roles = Role::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->with(['permissions' => function ($query) use ($moduleId) {
                $query->where('permissions.module_id', $moduleId)
                    ->select('permissions.id', 'permissions.name', 'permissions.page');
            }])
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->orderBy('page')
            ->orderBy('name')
            ->get()
            ->groupBy('page');

        $userRole = $this->currentRole($user);

        $userPermissions = $this->currentModuleDirectPermissions($user)
            ->groupBy('page')
            ->map(function ($perPage) {
                return $perPage->mapToGroups(function (Permission $permission) {
                    $parts = explode(' ', $permission->name);
                    $action = strtolower(array_shift($parts));
                    $resource = implode(' ', $parts);

                    return [$resource => [$action]];
                })->map(fn ($actions) => $actions->flatten()->unique()->values()->all());
            });

        $roleDefaults = $this->buildRoleDefaults($roles);

        return compact('user', 'roles', 'permissions', 'userPermissions', 'userRole', 'roleDefaults');
    }

    /**
     * Accepts the nested payload and optional role name.
     * Example $nested: ["Users" => ["User Lists" => ["view","modify"]], ...]
     */
    public function syncNestedPermissions(User $user, array $nested, ?string $roleName = null): int
    {
        $moduleId = $this->requireModuleId();
        $actor = auth()->user();

        if (! app(AdminContextAuthorizer::class)->canManageCurrentContextAccess($actor)) {
            abort(403, 'Only administrators may manage user roles and permissions.');
        }

        $this->ensureUserBelongsToCurrentScope($user);

        return DB::transaction(function () use ($user, $nested, $roleName, $moduleId) {
            $beforeRole = $this->currentRoleName($user);
            $beforePerms = $this->currentModuleDirectPermissionNames($user);

            if ($roleName) {
                $role = $this->resolveRoleForCurrentModule($moduleId, $roleName);
                $this->roleAssignments->sync($user, [$role]);

                $this->audit->record(
                    'user.role.changed',
                    $user,
                    ['role' => $beforeRole],
                    ['role' => $roleName],
                    $this->meta(),
                    'via nested permissions sync',
                    $this->buildRoleChangedDisplay($user, $beforeRole, $roleName)
                );

                Log::info('perm.update: role reset to defaults', [
                    'user_id' => $user->id,
                    'role' => $roleName,
                    'defaults' => array_slice($this->permissionNamesForRole($role, $moduleId), 0, 20),
                ]);
            }

            if (empty($nested)) {
                $this->syncDirectPermissionsForCurrentModule($user, [], $moduleId);

                $this->audit->record(
                    'user.permissions.synced',
                    $user,
                    ['direct_permissions' => $beforePerms],
                    ['direct_permissions' => []],
                    $this->meta(),
                    'cleared all direct permissions',
                    $this->buildPermissionsSyncedDisplay($user, $beforePerms, [])
                );

                Log::info('perm.update: cleared all direct permissions', ['user_id' => $user->id]);

                return 0;
            }

            $pagesRequested = array_slice(array_keys($nested), 0, 50);
            $pool = Permission::query()
                ->where('module_id', $moduleId)
                ->where('guard_name', 'web')
                ->whereNull('deleted_at')
                ->whereIn('page', $pagesRequested)
                ->get();

            Log::info('perm.update: pool summary', [
                'user_id' => $user->id,
                'pages_req' => $pagesRequested,
                'pool_count' => $pool->count(),
                'sample_names' => $pool->pluck('name')->take(20),
            ]);

            $dict = [];
            foreach ($pool as $permission) {
                [$actionRaw, $resourceRaw] = explode(' ', $permission->name, 2);
                $page = strtolower(trim((string) $permission->page));
                $action = $this->normalizeAction($actionRaw ?? '');
                $resource = strtolower(trim($resourceRaw ?? ''));
                $dict[$page][$resource][$action] = $permission->id;
            }

            $ids = [];
            $found = [];
            $misses = [];

            foreach ($nested as $page => $resources) {
                $pKey = strtolower(trim($page));
                foreach ($resources as $resource => $actions) {
                    $rKey = strtolower(trim($resource));
                    foreach ((array) $actions as $action) {
                        $aKey = $this->normalizeAction($action);
                        $id = $dict[$pKey][$rKey][$aKey] ?? null;

                        if ($id) {
                            $ids[] = $id;
                            if (count($found) < 25) {
                                $found[] = compact('pKey', 'rKey', 'aKey', 'id');
                            }
                            continue;
                        }

                        if (count($misses) < 25) {
                            $misses[] = compact('pKey', 'rKey', 'aKey');
                        }
                    }
                }
            }

            $ids = array_values(array_unique($ids));

            $this->syncDirectPermissionsForCurrentModule($user, $ids, $moduleId);
            $afterPerms = $this->currentModuleDirectPermissionNames($user);

            $this->audit->record(
                'user.permissions.synced',
                $user,
                ['direct_permissions' => $beforePerms],
                ['direct_permissions' => $afterPerms],
                [
                    ...$this->meta(),
                    'resolve_found_sample' => $found,
                    'resolve_miss_sample' => $misses,
                ],
                'via nested permissions UI',
                $this->buildPermissionsSyncedDisplay($user, $beforePerms, $afterPerms, $found, $misses)
            );

            Log::info('perm.update: mapping result', [
                'user_id' => $user->id,
                'apply_count' => count($ids),
                'found_sample' => $found,
                'miss_sample' => $misses,
            ]);

            return count($ids);
        });
    }

    /** Generate a cryptographically secure alphanumeric string (8 chars). */
    public function resetPasswordToTemporary(User $user): string
    {
        $this->ensureUserBelongsToCurrentScope($user);
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
            'temporary password generated by admin',
            $this->buildPasswordResetDisplay($user)
        );

        Log::info('password.reset: admin initiated temp password', [
            'user_id' => $user->id,
            'by' => auth()->id(),
        ]);

        return $temp;
    }

    /* ----------------------------- Helpers ------------------------------ */

    /** Normalize action names so UI "modify" maps to DB "edit", etc. */
    private function normalizeAction(string $action): string
    {
        $key = strtolower(trim($action));
        $aliases = [
            'modify' => 'edit',
            'edit' => 'edit',
            'update' => 'update',
            'view' => 'view',
            'show' => 'view',
            'create' => 'create',
            'add' => 'create',
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

    private function buildPermissionsSyncedDisplay(
        User $user,
        array $beforePerms,
        array $afterPerms,
        array $resolvedSelections = [],
        array $unresolvedSelections = []
    ): array {
        $added = array_values(array_diff($afterPerms, $beforePerms));
        $removed = array_values(array_diff($beforePerms, $afterPerms));
        $currentRole = $this->currentRoleName($user);

        $systemNotes = [];
        if ($resolvedSelections !== []) {
            $systemNotes[] = [
                'title' => 'Resolved selections',
                'items' => array_map(
                    fn (array $item): string => $this->formatSelectionPath($item),
                    $resolvedSelections
                ),
            ];
        }

        if ($unresolvedSelections !== []) {
            $systemNotes[] = [
                'title' => 'Unresolved selections',
                'items' => array_map(
                    fn (array $item): string => $this->formatSelectionPath($item),
                    $unresolvedSelections
                ),
            ];
        }

        return [
            'summary' => 'Permissions updated for ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Direct Permissions',
                    'items' => [
                        [
                            'label' => 'Added',
                            'value' => array_map([$this, 'formatPermissionLabel'], $added),
                        ],
                        [
                            'label' => 'Removed',
                            'value' => array_map([$this, 'formatPermissionLabel'], $removed),
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Role' => $currentRole ?: 'No Role Assigned',
                'Direct Permission Count' => count($afterPerms),
            ],
            'system_notes' => $systemNotes,
        ];
    }

    private function buildRoleChangedDisplay(User $user, ?string $beforeRole, ?string $afterRole): array
    {
        $afterRoleLabel = $afterRole ?: 'No Role Assigned';

        return [
            'summary' => 'Role updated for ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Role Assignment',
                    'items' => [
                        [
                            'label' => 'Role',
                            'before' => $beforeRole ?: 'No Role Assigned',
                            'after' => $afterRoleLabel,
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Current Role' => $afterRoleLabel,
            ],
        ];
    }

    private function buildRoleAssignedDisplay(User $user, string $roleName): array
    {
        return [
            'summary' => 'Default role assigned to ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Role Assignment',
                    'items' => [
                        [
                            'label' => 'Default Role',
                            'before' => 'No Role Assigned',
                            'after' => $roleName,
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Current Role' => $roleName,
            ],
        ];
    }

    private function buildUserArchivedDisplay(User $user, array $snapshot): array
    {
        return [
            'summary' => 'User archived: ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Account Lifecycle',
                    'items' => [
                        [
                            'label' => 'Archive Status',
                            'before' => 'Active Record',
                            'after' => 'Archived',
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Username' => $snapshot['username'] ?? 'None',
                'Email' => $snapshot['email'] ?? 'None',
                'Current Status' => ! empty($snapshot['is_active']) ? 'Active' : 'Inactive',
            ],
        ];
    }

    private function buildUserRestoredDisplay(User $user): array
    {
        return [
            'summary' => 'User restored: ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Account Lifecycle',
                    'items' => [
                        [
                            'label' => 'Archive Status',
                            'before' => 'Archived',
                            'after' => 'Active Record',
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Current Role' => $this->currentRoleName($user) ?: 'No Role Assigned',
            ],
        ];
    }

    private function buildStatusUpdatedDisplay(User $user, bool $before, bool $after): array
    {
        return [
            'summary' => 'Status updated for ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Account Status',
                    'items' => [
                        [
                            'label' => 'Status',
                            'before' => $before ? 'Active' : 'Inactive',
                            'after' => $after ? 'Active' : 'Inactive',
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Current Role' => $this->currentRoleName($user) ?: 'No Role Assigned',
            ],
        ];
    }

    private function buildPasswordResetDisplay(User $user): array
    {
        return [
            'summary' => 'Temporary password generated for ' . $this->userDisplayName($user),
            'subject_label' => $this->userDisplayName($user),
            'sections' => [
                [
                    'title' => 'Password Reset',
                    'items' => [
                        [
                            'label' => 'Temporary Password',
                            'value' => 'Generated by administrator',
                        ],
                        [
                            'label' => 'Password Change on Next Login',
                            'value' => 'Required',
                        ],
                    ],
                ],
            ],
            'request_details' => [
                'Username' => $user->username ?: 'None',
                'Email' => $user->email ?: 'None',
            ],
        ];
    }

    private function userDisplayName(User $user): string
    {
        $profileName = trim((string) ($user->profile?->full_name ?? ''));
        if ($profileName !== '') {
            return $profileName;
        }

        return (string) ($user->username ?: $user->email ?: 'User');
    }

    private function formatPermissionLabel(string $permission): string
    {
        return str($permission)
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->title()
            ->value();
    }

    private function formatSelectionPath(array $item): string
    {
        return collect([
            $item['pKey'] ?? null,
            $item['rKey'] ?? null,
            $item['aKey'] ?? null,
        ])
            ->filter(fn (mixed $value): bool => filled($value))
            ->map(fn (mixed $value): string => str((string) $value)->replaceMatches('/\s+/', ' ')->trim()->title()->value())
            ->implode(' / ');
    }

    private function buildRoleDefaults($roles): array
    {
        return $roles->mapWithKeys(function (Role $role): array {
            $nested = [];

            foreach ($role->permissions as $permission) {
                $page = $permission->page ?: 'Others';
                $words = explode(' ', $permission->name, 2);
                $action = $this->normalizeRoleDefaultAction($words[0] ?? '');
                $resource = trim((string) ($words[1] ?? ''));

                if ($resource === '') {
                    continue;
                }

                $nested[$page] ??= [];
                $nested[$page][$resource] ??= [];

                if (! in_array($action, $nested[$page][$resource], true)) {
                    $nested[$page][$resource][] = $action;
                }
            }

            return [$role->name => $nested];
        })->all();
    }

    private function normalizeRoleDefaultAction(string $action): string
    {
        return match (strtolower(trim($action))) {
            'edit', 'modify' => 'modify',
            default => strtolower(trim($action)),
        };
    }

    /**
     * Generate secure, unambiguous alphanumeric string.
     * Ensures at least one uppercase, one lowercase, and one digit.
     */
    private function generateAlphaNum(int $length = 8): string
    {
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower = 'abcdefghijkmnopqrstuvwxyz';
        $digits = '23456789';
        $alphabet = $upper . $lower . $digits;

        $max = strlen($alphabet) - 1;

        $buf = '';
        for ($i = 0; $i < $length; $i++) {
            $buf .= $alphabet[random_int(0, $max)];
        }

        $len = $length - 1;
        if (! preg_match('/[A-Z]/', $buf)) {
            $buf[random_int(0, $len)] = $upper[random_int(0, strlen($upper) - 1)];
        }
        if (! preg_match('/[a-z]/', $buf)) {
            $buf[random_int(0, $len)] = $lower[random_int(0, strlen($lower) - 1)];
        }
        if (! preg_match('/\d/', $buf)) {
            $buf[random_int(0, $len)] = $digits[random_int(0, strlen($digits) - 1)];
        }

        return $buf;
    }

    private function requireModuleId(): string
    {
        $moduleId = $this->context->moduleId();

        if (! $moduleId) {
            throw new \RuntimeException('Current module context is not available.');
        }

        return $moduleId;
    }

    private function currentRole(User $user): ?Role
    {
        $role = $this->roleAssignments->roles($user)->first();

        return $role instanceof Role ? $role : null;
    }

    private function currentRoleName(User $user): ?string
    {
        return $this->currentRole($user)?->name;
    }

    private function currentModuleDirectPermissions(User $user)
    {
        $moduleId = $this->requireModuleId();

        return Permission::query()
            ->select('permissions.*')
            ->join('model_has_permissions', function ($join) use ($user, $moduleId) {
                $join->on('model_has_permissions.permission_id', '=', 'permissions.id')
                    ->where('model_has_permissions.module_id', '=', $moduleId)
                    ->where('model_has_permissions.model_type', '=', User::class)
                    ->where('model_has_permissions.model_id', '=', $user->id);
            })
            ->where('permissions.module_id', $moduleId)
            ->where('permissions.guard_name', 'web')
            ->whereNull('permissions.deleted_at')
            ->orderBy('permissions.page')
            ->orderBy('permissions.name')
            ->get();
    }

    private function currentModuleDirectPermissionNames(User $user): array
    {
        return $this->currentModuleDirectPermissions($user)
            ->pluck('name')
            ->values()
            ->all();
    }

    private function permissionIdsForCurrentModuleByNames(string $moduleId, array $permissionNames): array
    {
        $names = array_values(array_unique(array_filter(
            array_map(static fn (mixed $permissionName): string => trim((string) $permissionName), $permissionNames)
        )));

        if ($names === []) {
            return [];
        }

        $permissionIds = Permission::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->whereIn('name', $names)
            ->pluck('id')
            ->values()
            ->all();

        if (count($permissionIds) !== count($names)) {
            throw ValidationException::withMessages([
                'permissions' => 'Selected permissions must belong to the current module.',
            ]);
        }

        return $permissionIds;
    }

    private function syncDirectPermissionsForCurrentModule(User $user, array $permissionIds, string $moduleId): void
    {
        $permissionIds = array_values(array_unique(array_filter(
            array_map(static fn (mixed $permissionId): string => trim((string) $permissionId), $permissionIds)
        )));

        $existingPermissions = ModelHasPermission::query()
            ->where('module_id', $moduleId)
            ->where('model_type', User::class)
            ->where('model_id', $user->id);

        if ($permissionIds === []) {
            $existingPermissions->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        $validPermissionIds = Permission::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->whereIn('id', $permissionIds)
            ->pluck('id')
            ->values()
            ->all();

        if (count($validPermissionIds) !== count($permissionIds)) {
            throw ValidationException::withMessages([
                'permissions' => 'Selected permissions must belong to the current module.',
            ]);
        }

        $existingPermissions
            ->whereNotIn('permission_id', $validPermissionIds)
            ->delete();

        foreach ($validPermissionIds as $permissionId) {
            ModelHasPermission::query()->updateOrCreate(
                [
                    'module_id' => $moduleId,
                    'permission_id' => $permissionId,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ],
                []
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function resolveRoleForCurrentModule(string $moduleId, string $roleName): Role
    {
        $role = Role::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->where('name', trim($roleName))
            ->first();

        if (! $role) {
            throw ValidationException::withMessages([
                'role' => 'Selected role must belong to the current module.',
            ]);
        }

        return $role;
    }

    private function permissionNamesForRole(Role $role, string $moduleId): array
    {
        return $role->permissions()
            ->where('permissions.module_id', $moduleId)
            ->pluck('permissions.name')
            ->values()
            ->all();
    }

    private function ensureUserBelongsToCurrentScope(User $user): void
    {
        $adminRoutes = app(AdminRouteResolver::class);

        if (! $adminRoutes->isModuleScoped()) {
            return;
        }

        $moduleId = $this->requireModuleId();
        $belongsToModule = $user->userModules()
            ->where('module_id', $moduleId)
            ->where('is_active', true)
            ->exists();

        if ($belongsToModule) {
            return;
        }

        throw (new ModelNotFoundException())->setModel(User::class, [$user->getKey()]);
    }
}
