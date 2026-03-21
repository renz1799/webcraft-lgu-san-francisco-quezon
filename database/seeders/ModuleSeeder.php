<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use App\Core\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $defaultDepartmentId = Department::query()
            ->where('code', $this->configuredDepartmentCode((string) config('module.code')))
            ->value('id');

        Module::updateOrCreate(
            ['id' => config('module.id')],
            [
                'code' => config('module.code'),
                'name' => config('module.name'),
                'description' => 'System module seeded from application configuration.',
                'url' => config('app.url'),
                'default_department_id' => $defaultDepartmentId,
                'is_active' => true,
            ]
        );
    }

    private function configuredDepartmentCode(string $moduleCode): string
    {
        $moduleCode = strtoupper(trim($moduleCode));

        return trim((string) data_get(
            config('modules.department_defaults', []),
            $moduleCode . '.code',
            config('department.code')
        ));
    }
}
