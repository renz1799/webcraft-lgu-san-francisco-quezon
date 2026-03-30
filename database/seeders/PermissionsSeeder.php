<?php

namespace Database\Seeders;

use Database\Seeders\Modules\GSO\GsoRolePermissionSeeder;
use Database\Seeders\Modules\GSO\GsoPermissionSeeder;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CorePermissionSeeder::class,
            GsoPermissionSeeder::class,
            LegacyPermissionAssignmentMigrationSeeder::class,
            CoreRolePermissionSeeder::class,
            GsoRolePermissionSeeder::class,
        ]);

        $this->command?->info('Permissions seeded successfully (normalized catalogs + migrated legacy assignments + admin bundles).');
    }
}
