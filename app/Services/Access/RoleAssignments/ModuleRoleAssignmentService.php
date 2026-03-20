<?php

namespace App\Services\Access\RoleAssignments;

use App\Models\ModelHasPermission;
use App\Models\ModelHasRole;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModule;
use App\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Support\CurrentContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\PermissionRegistrar;

class ModuleRoleAssignmentService implements ModuleRoleAssignmentServiceInterface
{
    public function __construct(
        private readonly CurrentContext $context,
    ) {}

    public function assign(User $user, string|Role $role): void
    {
        $moduleId = $this->requireModuleId();

        $this->ensureUserHasModuleAccess($user, $moduleId);

        $roleModel = $this->resolveRole($role, $moduleId);

        DB::transaction(function () use ($user, $moduleId, $roleModel) {
            ModelHasRole::query()->updateOrCreate(
                [
                    'module_id' => $moduleId,
                    'role_id' => $roleModel->id,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ],
                []
            );

            $this->syncModulePermissions($user, $moduleId);
        });
    }

    public function sync(User $user, array $roles): void
    {
        $moduleId = $this->requireModuleId();

        $this->ensureUserHasModuleAccess($user, $moduleId);

        $roleIds = collect($roles)
            ->map(fn ($role) => $this->resolveRole($role, $moduleId)->id)
            ->unique()
            ->values();

        DB::transaction(function () use ($user, $moduleId, $roleIds) {
            ModelHasRole::query()
                ->where('module_id', $moduleId)
                ->where('model_type', User::class)
                ->where('model_id', $user->id)
                ->whereNotIn('role_id', $roleIds->all())
                ->delete();

            foreach ($roleIds as $roleId) {
                ModelHasRole::query()->updateOrCreate(
                    [
                        'module_id' => $moduleId,
                        'role_id' => $roleId,
                        'model_type' => User::class,
                        'model_id' => $user->id,
                    ],
                    []
                );
            }

            $this->syncModulePermissions($user, $moduleId);
        });
    }

    public function revoke(User $user, string|Role $role): void
    {
        $moduleId = $this->requireModuleId();
        $roleModel = $this->resolveRole($role, $moduleId);

        DB::transaction(function () use ($user, $moduleId, $roleModel) {
            ModelHasRole::query()
                ->where('module_id', $moduleId)
                ->where('role_id', $roleModel->id)
                ->where('model_type', User::class)
                ->where('model_id', $user->id)
                ->delete();

            $this->syncModulePermissions($user, $moduleId);
        });
    }

    public function revokeAll(User $user): void
    {
        $moduleId = $this->requireModuleId();

        DB::transaction(function () use ($user, $moduleId) {
            ModelHasRole::query()
                ->where('module_id', $moduleId)
                ->where('model_type', User::class)
                ->where('model_id', $user->id)
                ->delete();

            $this->syncModulePermissions($user, $moduleId);
        });
    }

    public function roles(User $user): Collection
    {
        $moduleId = $this->requireModuleId();

        return Role::query()
            ->select('roles.*')
            ->join('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.module_id', $moduleId)
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.model_id', $user->id)
            ->where('roles.module_id', $moduleId)
            ->get();
    }

    public function hasRole(User $user, string $roleName): bool
    {
        $moduleId = $this->requireModuleId();

        return Role::query()
            ->join('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.module_id', $moduleId)
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.model_id', $user->id)
            ->where('roles.module_id', $moduleId)
            ->where('roles.name', $roleName)
            ->exists();
    }

    private function resolveRole(string|Role $role, string $moduleId): Role
    {
        if ($role instanceof Role) {
            if ($role->module_id !== $moduleId) {
                throw new RuntimeException('Role does not belong to the current module.');
            }

            return $role;
        }

        $roleModel = Role::query()
            ->where('module_id', $moduleId)
            ->where('name', $role)
            ->first();

        if (! $roleModel) {
            throw new RuntimeException("Role [{$role}] was not found in the current module.");
        }

        return $roleModel;
    }

    private function ensureUserHasModuleAccess(User $user, string $moduleId): void
    {
        $hasAccess = UserModule::query()
            ->where('user_id', $user->id)
            ->where('module_id', $moduleId)
            ->where('is_active', true)
            ->exists();

        if (! $hasAccess) {
            throw new RuntimeException('User does not have active access to the current module.');
        }
    }

    private function requireModuleId(): string
    {
        $moduleId = $this->context->moduleId();

        if (! $moduleId) {
            throw new RuntimeException('Current module context is not available.');
        }

        return $moduleId;
    }

    private function syncModulePermissions(User $user, string $moduleId): void
    {
        $roleIds = ModelHasRole::query()
            ->where('module_id', $moduleId)
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->pluck('role_id')
            ->unique()
            ->values();

        $permissionIds = DB::table('role_has_permissions')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->whereIn('role_has_permissions.role_id', $roleIds->all())
            ->where('permissions.module_id', $moduleId)
            ->pluck('permissions.id')
            ->unique()
            ->values();

        $existingPermissions = ModelHasPermission::query()
            ->where('module_id', $moduleId)
            ->where('model_type', User::class)
            ->where('model_id', $user->id);

        if ($permissionIds->isEmpty()) {
            $existingPermissions->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            return;
        }

        $existingPermissions
            ->whereNotIn('permission_id', $permissionIds->all())
            ->delete();

        foreach ($permissionIds as $permissionId) {
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
}
