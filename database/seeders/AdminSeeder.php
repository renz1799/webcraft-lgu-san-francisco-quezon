<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Step 1: Create the role (without creating permissions)
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

            // Step 2: Create an admin user
            $adminUser = User::updateOrCreate(
                ['email' => 'admin@webcraft.ph'], // Match user by email
                [
                    'id' => Str::uuid(), // Generate UUID for the user
                    'username' => 'admin',
                    'password' => Hash::make('password'), // Set default password
                    'user_type' => 'Administrator',
                    'is_active' => true,
                ]
            );

            // Debug: Check if the user was created successfully
            if (!$adminUser->id) {
                throw new \Exception('Admin user creation failed!');
            }

            // Step 3: Assign the admin role to the user
            $adminUser->assignRole($adminRole);

            // Commit the transaction
            DB::commit();

            // Console output
            $this->command->info('✅ Admin user created:');
            $this->command->info('🔹 Username: admin');
            $this->command->info('🔹 Email: admin@webcraft.ph');
            $this->command->info('🔹 Password: password');
        } catch (\Exception $e) {
            // Rollback the transaction on failure
            DB::rollback();

            // Output the error to the console
            $this->command->error('❌ Seeder failed: ' . $e->getMessage());
        }
    }
}
