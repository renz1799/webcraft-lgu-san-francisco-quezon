<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Support\CurrentContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $context = app(CurrentContext::class);
        $moduleId = $context->moduleId();

        if (! $moduleId) {
            throw new \RuntimeException('PermissionsSeeder: current module not found. Run ModuleSeeder first.');
        }

        $permissions = [
            ['name' => 'view Tasks', 'page' => 'Manage Tasks'],
            ['name' => 'modify Reassign Tasks', 'page' => 'Manage Tasks'],
        ];

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

        $this->command?->info('Permissions seeded successfully.');
    }
}