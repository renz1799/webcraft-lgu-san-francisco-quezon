<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Step 1: Define permissions
        $permissions = [
            'manage users',
            'manage permissions',
        ];

        // Step 2: Create or fetch permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Step 3: Create or fetch the admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Step 4: Assign all permissions to the admin role
        $adminRole->syncPermissions($permissions);

        // Step 5: Fetch the user by UUID and assign the admin role
        $user = User::find('a53a21e6-68e7-4410-9b7a-22b09781330f');

        if ($user) {
            $user->assignRole($adminRole);
            $this->command->info('Admin role assigned to user with UUID: ' . $user->id);
        } else {
            $this->command->warn('User with UUID a53a21e6-68e7-4410-9b7a-22b09781330f not found.');
        }
    }
}
//php artisan db:seed --class=AdminRoleSeeder
