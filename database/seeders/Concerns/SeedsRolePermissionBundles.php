<?php

namespace Database\Seeders\Concerns;

use App\Core\Models\Module;
use App\Core\Models\Permission;
use App\Core\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

trait SeedsRolePermissionBundles
{
    protected function syncRoleToAllModulePermissions(string $moduleCode, string $roleName): void
    {
        $module = $this->resolveModule($moduleCode);
        $permissionIds = Permission::query()
            ->where('module_id', $module->id)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->all();

        $role = $this->resolveRole((string) $module->id, $roleName);

        $role->permissions()->sync($permissionIds);
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    protected function syncRoleAliasesToAllModulePermissions(string $moduleCode, array $roleNames): void
    {
        $module = $this->resolveModule($moduleCode);
        $permissionIds = Permission::query()
            ->where('module_id', $module->id)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->all();

        $normalizedRoleNames = collect($roleNames)
            ->map(static fn (mixed $roleName): string => trim((string) $roleName))
            ->filter()
            ->unique()
            ->values();

        if ($normalizedRoleNames->isEmpty()) {
            return;
        }

        foreach ($normalizedRoleNames as $index => $roleName) {
            $role = $index === 0
                ? $this->resolveRole((string) $module->id, $roleName)
                : $this->findRole((string) $module->id, $roleName);

            if (! $role) {
                continue;
            }

            $role->permissions()->sync($permissionIds);
        }
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    protected function syncRoleToNamedPermissions(string $moduleCode, string $roleName, array $permissionNames): void
    {
        $module = $this->resolveModule($moduleCode);
        $role = $this->resolveRole((string) $module->id, $roleName);

        $normalizedNames = collect($permissionNames)
            ->map(static fn (mixed $name): string => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        if ($normalizedNames->isEmpty()) {
            $role->permissions()->sync([]);

            return;
        }

        $permissions = Permission::query()
            ->where('module_id', $module->id)
            ->where('guard_name', 'web')
            ->whereIn('name', $normalizedNames->all())
            ->get(['id', 'name']);

        $found = $permissions->pluck('name')->values();
        $missing = $normalizedNames->diff($found)->values();

        if ($missing->isNotEmpty()) {
            throw new RuntimeException(static::class . ': missing permissions for role [' . $roleName . '] in [' . $moduleCode . ']: ' . $missing->implode(', '));
        }

        $role->permissions()->sync($permissions->pluck('id')->all());
    }

    private function resolveModule(string $moduleCode): Module
    {
        $module = Module::query()
            ->where('code', $moduleCode)
            ->first();

        if (! $module) {
            throw new RuntimeException(static::class . ": {$moduleCode} module not found. Run ModuleSeeder first.");
        }

        return $module;
    }

    private function resolveRole(string $moduleId, string $roleName): Role
    {
        return Role::query()->firstOrCreate(
            [
                'module_id' => $moduleId,
                'name' => $roleName,
                'guard_name' => 'web',
            ],
            [
                'id' => (string) Str::uuid(),
            ]
        );
    }

    private function findRole(string $moduleId, string $roleName): ?Role
    {
        return Role::query()
            ->where('module_id', $moduleId)
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->first();
    }
}
