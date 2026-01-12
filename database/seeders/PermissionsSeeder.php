<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'view User Permissions', 'page' => 'Manage Roles/Permissions'],
            ['name' => 'modify User Permissions', 'page' => 'Manage Roles/Permissions'],
            ['name' => 'delete User Permissions', 'page' => 'Manage Roles/Permissions'],

        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['name' => $perm['name'], 'page' => $perm['page']],
                ['guard_name' => 'web', 'id' => Str::uuid()]
            );
        }

        echo "✅ Permissions seeded successfully!\n";
    }
}
