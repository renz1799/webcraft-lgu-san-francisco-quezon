<?php

namespace App\Core\Support;

use App\Core\Models\Permission;
use App\Core\Models\User;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdminContextAuthorizer
{
    private const PLATFORM_ACCESS_MANAGEMENT_PERMISSIONS = [
        'users.manage_access',
        'roles.create',
        'roles.update',
        'roles.archive',
        'roles.restore',
        'permissions.create',
        'permissions.update',
        'permissions.archive',
        'permissions.restore',
    ];

    private const MODULE_ACCESS_MANAGEMENT_PERMISSIONS = [
        'access.users.manage',
        'access.roles.manage',
        'access.permissions.manage',
    ];

    private const PLATFORM_USER_REGISTRATION_PERMISSIONS = [
        'users.create',
    ];

    public function __construct(
        private readonly CurrentContext $context,
        private readonly ModuleAccessServiceInterface $moduleAccess,
        private readonly ModuleRoleAssignmentServiceInterface $moduleRoles,
    ) {}

    public function hasCurrentContextAccess(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $moduleId = $this->context->moduleId();

        return is_string($moduleId) && $moduleId !== ''
            && $this->moduleAccess->hasActiveModuleAccess($user, $moduleId);
    }

    public function canManageCurrentContextAccess(?User $user): bool
    {
        $permissions = $this->isPlatformContext()
            ? self::PLATFORM_ACCESS_MANAGEMENT_PERMISSIONS
            : self::MODULE_ACCESS_MANAGEMENT_PERMISSIONS;

        return $this->allowsAnyPermission($user, $permissions);
    }

    public function canRegisterUsers(?User $user): bool
    {
        return $this->isPlatformContext()
            && $this->allowsAnyPermission($user, self::PLATFORM_USER_REGISTRATION_PERMISSIONS);
    }

    public function canViewCurrentContextAuditLogs(?User $user): bool
    {
        return $this->allowsPermission($user, 'audit_logs.view');
    }

    public function canRestoreCurrentContextAuditData(?User $user): bool
    {
        return $this->allowsPermission($user, 'audit_logs.restore_data');
    }

    public function canViewPlatformLoginLogs(?User $user): bool
    {
        return $this->isPlatformContext()
            && $this->allowsPermission($user, 'login_logs.view');
    }

    public function allowsAny(?User $user, array|string $rolesOrPermissions): bool
    {
        return $this->hasAnyPermission($user, $rolesOrPermissions)
            || $this->hasAnyRole($user, $rolesOrPermissions);
    }

    public function allowsPermission(?User $user, string $permission): bool
    {
        return $this->allowsAnyPermission($user, [$permission]);
    }

    public function allowsAnyPermission(?User $user, array|string $permissions): bool
    {
        return $this->hasAnyPermission($user, $permissions);
    }

    public function allowsAllPermissions(?User $user, array|string $permissions): bool
    {
        $moduleId = $this->context->moduleId();

        return is_string($moduleId) && $moduleId !== ''
            && $this->allowsAllPermissionsInModule($user, $permissions, $moduleId);
    }

    public function allowsPermissionInModule(?User $user, string $permission, string $moduleId): bool
    {
        return $this->allowsAnyPermissionInModule($user, [$permission], $moduleId);
    }

    public function allowsAnyPermissionInModule(?User $user, array|string $permissions, string $moduleId): bool
    {
        if (! $this->userHasActiveModuleAccess($user, $moduleId)) {
            return false;
        }

        $normalizedPermissions = $this->normalizeTokens($permissions);

        if ($normalizedPermissions === []) {
            return false;
        }

        return $this->permissionQueryForModule($user, $moduleId, $normalizedPermissions)->exists();
    }

    public function allowsAllPermissionsInModule(?User $user, array|string $permissions, string $moduleId): bool
    {
        if (! $this->userHasActiveModuleAccess($user, $moduleId)) {
            return false;
        }

        $normalizedPermissions = $this->normalizeTokens($permissions);

        if ($normalizedPermissions === []) {
            return false;
        }

        $matchedCount = $this->permissionQueryForModule($user, $moduleId, $normalizedPermissions)
            ->distinct('permissions.id')
            ->count('permissions.id');

        return $matchedCount === count($normalizedPermissions);
    }

    public function hasAnyRole(?User $user, array|string $roles): bool
    {
        $moduleId = $this->context->moduleId();

        return is_string($moduleId) && $moduleId !== ''
            ? $this->hasAnyRoleInModule($user, $roles, $moduleId)
            : false;
    }

    public function hasAnyRoleInModule(?User $user, array|string $roles, string $moduleId): bool
    {
        if (! $this->userHasActiveModuleAccess($user, $moduleId)) {
            return false;
        }

        $normalizedRoles = $this->normalizeTokens($roles);

        if ($normalizedRoles === []) {
            return false;
        }

        if ($this->isPlatformContext() && $this->containsAdministratorAlias($normalizedRoles)) {
            return true;
        }

        foreach ($normalizedRoles as $role) {
            try {
                if ($this->moduleRoles->hasRoleInModule($user, $role, $moduleId)) {
                    return true;
                }
            } catch (RuntimeException) {
                return false;
            }
        }

        return false;
    }

    public function hasAnyPermission(?User $user, array|string $permissions): bool
    {
        $moduleId = $this->context->moduleId();

        return is_string($moduleId) && $moduleId !== ''
            ? $this->allowsAnyPermissionInModule($user, $permissions, $moduleId)
            : false;
    }

    public function isPlatformContext(): bool
    {
        return (bool) $this->context->module()?->isPlatformContext();
    }

    private function normalizeTokens(array|string $tokens): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $token): string => trim((string) $token),
            Arr::wrap($tokens)
        ))));
    }

    private function userHasActiveModuleAccess(?User $user, string $moduleId): bool
    {
        return $user
            && $moduleId !== ''
            && $this->moduleAccess->hasActiveModuleAccess($user, $moduleId);
    }

    private function permissionQueryForModule(?User $user, string $moduleId, array $normalizedPermissions)
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $permissionPivotKey = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $rolePivotKey = $columnNames['role_pivot_key'] ?? 'role_id';
        $modelKey = $columnNames['model_morph_key'] ?? 'model_id';

        return Permission::query()
            ->where('module_id', $moduleId)
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->whereIn('name', $normalizedPermissions)
            ->where(function ($query) use (
                $user,
                $moduleId,
                $tableNames,
                $permissionPivotKey,
                $rolePivotKey,
                $modelKey
            ) {
                $query->whereExists(function (QueryBuilder $direct) use ($user, $moduleId, $tableNames, $permissionPivotKey, $modelKey) {
                    $direct->selectRaw('1')
                        ->from($tableNames['model_has_permissions'])
                        ->whereColumn($tableNames['model_has_permissions'].'.'.$permissionPivotKey, 'permissions.id')
                        ->where($tableNames['model_has_permissions'].'.module_id', $moduleId)
                        ->where($tableNames['model_has_permissions'].'.model_type', User::class)
                        ->where($tableNames['model_has_permissions'].'.'.$modelKey, $user->id);
                })->orWhereExists(function (QueryBuilder $rolePermission) use (
                    $user,
                    $moduleId,
                    $tableNames,
                    $permissionPivotKey,
                    $rolePivotKey,
                    $modelKey
                ) {
                    $rolePermission->selectRaw('1')
                        ->from($tableNames['model_has_roles'])
                        ->join($tableNames['roles'], $tableNames['roles'].'.id', '=', $tableNames['model_has_roles'].'.'.$rolePivotKey)
                        ->join($tableNames['role_has_permissions'], $tableNames['role_has_permissions'].'.'.$rolePivotKey, '=', $tableNames['roles'].'.id')
                        ->whereColumn($tableNames['role_has_permissions'].'.'.$permissionPivotKey, 'permissions.id')
                        ->where($tableNames['model_has_roles'].'.module_id', $moduleId)
                        ->where($tableNames['model_has_roles'].'.model_type', User::class)
                        ->where($tableNames['model_has_roles'].'.'.$modelKey, $user->id)
                        ->where($tableNames['roles'].'.module_id', $moduleId);
                });
            });
    }

    private function containsAdministratorAlias(array $roles): bool
    {
        foreach ($roles as $role) {
            $normalizedRole = mb_strtolower($role);

            if (in_array($normalizedRole, ['administrator', 'admin'], true)) {
                return true;
            }
        }

        return false;
    }

}
