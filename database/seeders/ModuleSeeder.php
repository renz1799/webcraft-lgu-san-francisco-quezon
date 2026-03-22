<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use App\Core\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $registry = collect(config('modules.registry', []));

        if ($registry->isEmpty()) {
            $registry = collect([
                (string) config('module.code') => [
                    'code' => (string) config('module.code'),
                    'name' => (string) config('module.name'),
                    'description' => 'System module seeded from application configuration.',
                ],
            ]);
        }

        $configuredPrimaryId = (string) config('module.id');
        $configuredPrimaryCode = strtoupper(trim((string) config('module.code')));

        foreach ($registry as $registryCode => $definition) {
            $code = strtoupper(trim((string) ($definition['code'] ?? $registryCode)));
            $defaultDepartmentId = Department::query()
                ->where('code', $this->configuredDepartmentCode($code))
                ->value('id');

            Module::updateOrCreate(
                ['code' => $code],
                [
                    'id' => $code === $configuredPrimaryCode && $configuredPrimaryId !== ''
                        ? $configuredPrimaryId
                        : (Module::query()->where('code', $code)->value('id') ?: (string) \Illuminate\Support\Str::uuid()),
                    'name' => (string) ($definition['name'] ?? $code),
                    'type' => (string) ($definition['type'] ?? ($code === 'CORE' ? Module::TYPE_PLATFORM : Module::TYPE_BUSINESS)),
                    'description' => (string) ($definition['description'] ?? 'Platform module seeded from configuration.'),
                    'url' => rtrim((string) config('app.url'), '/') . ($code === 'CORE' ? '' : '/' . strtolower($code)),
                    'default_department_id' => $defaultDepartmentId,
                    'is_active' => true,
                ]
            );
        }
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
