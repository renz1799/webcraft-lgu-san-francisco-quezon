<?php

namespace Database\Seeders;

use App\Core\Models\Module;
use App\Core\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissionsByModule = [
            'CORE' => [
                ['name' => 'view Tasks', 'page' => 'Shared Tasks'],
                ['name' => 'view All Tasks', 'page' => 'Shared Tasks'],
                ['name' => 'modify Reassign Tasks', 'page' => 'Shared Tasks'],
            ],
            'GSO' => [
                ['name' => 'view Asset Types', 'page' => 'GSO Reference Data'],
                ['name' => 'modify Asset Types', 'page' => 'GSO Reference Data'],
                ['name' => 'view Asset Categories', 'page' => 'GSO Reference Data'],
                ['name' => 'modify Asset Categories', 'page' => 'GSO Reference Data'],
                ['name' => 'view Departments', 'page' => 'GSO Reference Data'],
                ['name' => 'modify Departments', 'page' => 'GSO Reference Data'],
                ['name' => 'view Fund Clusters', 'page' => 'GSO Reference Data'],
                ['name' => 'modify Fund Clusters', 'page' => 'GSO Reference Data'],
                ['name' => 'view Fund Sources', 'page' => 'GSO Reference Data'],
                ['name' => 'modify Fund Sources', 'page' => 'GSO Reference Data'],
                ['name' => 'view Accountable Officers', 'page' => 'GSO Reference Data'],
                ['name' => 'modify Accountable Officers', 'page' => 'GSO Reference Data'],
                ['name' => 'view AIR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'modify AIR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'view RIS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'create RIS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'modify RIS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'submit RIS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'approve RIS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'reject RIS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'reopen RIS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'revert RIS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'delete RIS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'restore RIS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'view PAR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'modify PAR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'restore PAR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'view ICS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'modify ICS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'restore ICS', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'view PTR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'modify PTR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'restore PTR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'view ITR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'modify ITR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'restore ITR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'view WMR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'modify WMR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'restore WMR', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'view Items', 'page' => 'GSO Inventory Foundation'],
                ['name' => 'modify Items', 'page' => 'GSO Inventory Foundation'],
                ['name' => 'view Inventory Items', 'page' => 'GSO Inventory Foundation'],
                ['name' => 'modify Inventory Items', 'page' => 'GSO Inventory Foundation'],
                ['name' => 'view Inspections', 'page' => 'GSO Workflow Foundation'],
                ['name' => 'modify Inspections', 'page' => 'GSO Workflow Foundation'],
            ],
        ];

        foreach ($permissionsByModule as $moduleCode => $permissions) {
            $moduleId = Module::query()
                ->where('code', $moduleCode)
                ->value('id');

            if (! $moduleId) {
                throw new RuntimeException("PermissionsSeeder: {$moduleCode} module not found. Run ModuleSeeder first.");
            }

            foreach ($permissions as $permission) {
                Permission::query()->updateOrCreate(
                    [
                        'module_id' => $moduleId,
                        'name' => $permission['name'],
                        'guard_name' => 'web',
                    ],
                    [
                        'id' => (string) Str::uuid(),
                        'page' => $permission['page'],
                    ]
                );
            }
        }

        $this->command?->info('Permissions seeded successfully.');
    }
}
