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
        return $this->hasAnyRole($user, ['Administrator', 'admin']);
    }

    public function canRegisterUsers(?User $user): bool
    {
        return $this->isPlatformContext() && $this->canManageCurrentContextAccess($user);
    }

    public function canViewCurrentContextAuditLogs(?User $user): bool
    {
        return $this->allowsAny($user, ['Administrator', 'admin', 'view Audit Logs']);
    }

    public function canRestoreCurrentContextAuditData(?User $user): bool
    {
        return $this->allowsAny($user, ['Administrator', 'admin', 'modify Allow Data Restoration']);
    }

    public function canViewPlatformLoginLogs(?User $user): bool
    {
        return $this->isPlatformContext()
            && $this->allowsAny($user, ['Administrator', 'admin', 'view Login Logs']);
    }

    public function allowsAny(?User $user, array|string $rolesOrPermissions): bool
    {
        return $this->hasAnyRole($user, $rolesOrPermissions)
            || $this->hasAnyPermission($user, $rolesOrPermissions);
    }

    public function hasAnyRole(?User $user, array|string $roles): bool
    {
        if (! $this->hasCurrentContextAccess($user)) {
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
                if ($this->moduleRoles->hasRole($user, $role)) {
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
        if (! $this->hasCurrentContextAccess($user)) {
            return false;
        }

        $moduleId = $this->context->moduleId();
        $normalizedPermissions = $this->normalizeTokens($permissions);

        if (! $moduleId || $normalizedPermissions === []) {
            return false;
        }

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
            })
            ->exists();
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
