<?php

namespace Database\Seeders\Concerns;

use App\Core\Models\Module;
use App\Core\Models\Permission;
use Illuminate\Support\Str;
use RuntimeException;

trait SeedsModulePermissions
{
    /**
     * @param  array<string, array<int, string|array{name:string,page?:string}>>  $groupedPermissions
     */
    protected function seedGroupedPermissions(string $moduleCode, array $groupedPermissions): void
    {
        $moduleId = Module::query()
            ->where('code', $moduleCode)
            ->value('id');

        if (! $moduleId) {
            throw new RuntimeException(static::class . ": {$moduleCode} module not found. Run ModuleSeeder first.");
        }

        foreach ($groupedPermissions as $page => $permissions) {
            foreach ($permissions as $permission) {
                $name = is_array($permission)
                    ? trim((string) ($permission['name'] ?? ''))
                    : trim((string) $permission);

                $resolvedPage = is_array($permission)
                    ? trim((string) ($permission['page'] ?? $page))
                    : trim((string) $page);

                if ($name === '' || $resolvedPage === '') {
                    continue;
                }

                $model = Permission::query()->firstOrNew([
                    'module_id' => $moduleId,
                    'name' => $name,
                    'guard_name' => 'web',
                ]);

                if (! $model->exists && empty($model->id)) {
                    $model->id = (string) Str::uuid();
                }

                $model->page = $resolvedPage;
                $model->save();
            }
        }
    }
}
