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
            ['name' => 'view User Permissions', 'page' => 'Manage Permissions'],
            ['name' => 'modify User Permissions', 'page' => 'Manage Permissions'],
            ['name' => 'delete User Permissions', 'page' => 'Manage Permissions'],

            ['name' => 'view User Registration', 'page' => 'Manage User Registration'],

            ['name' => 'view User Lists', 'page' => 'Manage Users'],
            ['name' => 'modify User Lists', 'page' => 'Manage Users'],
            ['name' => 'delete User Lists', 'page' => 'Manage Users'],

            ['name' => 'view Login Logs', 'page' => 'Manage Log in Logs'],
            ['name' => 'modify Login Logs', 'page' => 'Manage Log in Logs'],
            ['name' => 'delete Login Logs', 'page' => 'Manage Log in Logs'],

            ['name' => 'view Audit Logs', 'page' => 'Manage Audit Logs'],
            ['name' => 'modify Audit Logs', 'page' => 'Manage Audit Logs'],
            ['name' => 'delete Audit Logs', 'page' => 'Manage Audit Logs'],
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
