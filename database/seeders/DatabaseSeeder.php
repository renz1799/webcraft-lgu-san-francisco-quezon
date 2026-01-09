<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Register the AdminRoleSeeder
        $this->call(UserSeeder::class);
        $this->call(PermissionsSeeder::class);
        $this->call(NotificationSeeder::class);
    }
}
//php artisan migrate:fresh --seed
