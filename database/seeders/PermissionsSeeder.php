<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'view Tasks', 'page' => 'Manage Tasks'],
            ['name' => 'modify Reassign Tasks', 'page' => 'Manage Tasks'],
        ];

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                [
                    'name' => $permission['name'],
                    'page' => $permission['page'],
                ],
                [
                    'guard_name' => 'web',
                    'id' => (string) Str::uuid(),
                ]
            );
        }

        $this->command?->info('Permissions seeded successfully.');
    }
}