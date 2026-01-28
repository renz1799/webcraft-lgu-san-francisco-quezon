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
            ['name' => 'modify Reassign Tasks', 'page' => 'Manage Tasks'],
          //  ['name' => 'modify Tasks', 'page' => 'Manage Tasks'],
          //  ['name' => 'delete Tasks', 'page' => 'Manage Tasks'],

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
//php artisan db:seed --class=PermissionsSeeder