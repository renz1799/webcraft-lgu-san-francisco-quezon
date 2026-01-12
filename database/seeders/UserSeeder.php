<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;


class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // Ensure roles exist
            $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
            $staffRole = Role::firstOrCreate(['name' => 'Staff']);

            /**
             * ADMIN
             */
            $admin = User::factory()
                ->admin()
                ->create();

            UserProfile::factory()->create([
                'user_id' => $admin->id,
                'first_name' => 'System',
                'last_name' => 'Administrator',
            ]);

            $admin->assignRole($adminRole);

            /**
             * STAFF (bulk)
             */
            $staffUsers = User::factory()
                ->count(5)
                ->mustChangePassword()
                ->create();

            foreach ($staffUsers as $staff) {
                UserProfile::factory()->create([
                    'user_id' => $staff->id,
                ]);

                $staff->assignRole($staffRole);
            }
        });
    }
}
