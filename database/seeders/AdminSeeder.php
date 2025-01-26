<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Step 1: Create the permission
            $permission = Permission::updateOrCreate(
                ['name' => 'view permissions'], // Match permission by name
                [
                    'id' => Str::uuid(), // Generate UUID if creating a new permission
                    'guard_name' => 'web',
                ]
            );

            // Debug: Check if the permission was created successfully
            if (!$permission->id) {
                throw new \Exception('Permission creation failed!');
            }

            // Step 2: Create the role
            $adminRole = Role::updateOrCreate(
                ['name' => 'admin'], // Match role by name
                [
                    'id' => Str::uuid(), // Generate UUID if creating a new role
                    'guard_name' => 'web',
                ]
            );

            // Debug: Check if the role was created successfully
            if (!$adminRole->id) {
                throw new \Exception('Role creation failed!');
            }

            // Step 3: Insert into the pivot table manually
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permission->id,
                'role_id' => $adminRole->id,
            ]);

            // Debug: Check if data was inserted into the pivot table
            $pivotData = DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->where('role_id', $adminRole->id)
                ->first();

            if (!$pivotData) {
                throw new \Exception('Pivot table insertion failed!');
            }

            // Step 4: Create an admin user
            $adminUser = User::updateOrCreate(
                ['email' => 'admin@example.com'], // Match user by email
                [
                    'id' => Str::uuid(), // Generate UUID for the user
                    'username' => 'admin',
                    'password' => Hash::make('password'), // Set default password
                    'user_type' => 'admin',
                    'is_active' => true,
                ]
            );

            // Debug: Check if the user was created successfully
            if (!$adminUser->id) {
                throw new \Exception('Admin user creation failed!');
            }

            // Step 5: Assign the admin role to the user
            $adminUser->assignRole($adminRole);

            // Commit the transaction
            DB::commit();

            // Console output
            $this->command->info('Admin user created:');
            $this->command->info('Username: admin');
            $this->command->info('Email: admin@example.com');
            $this->command->info('Password: password');
        } catch (\Exception $e) {
            // Rollback the transaction on failure
            DB::rollback();

            // Output the error to the console
            $this->command->error('Seeder failed: ' . $e->getMessage());
        }
    }
}
