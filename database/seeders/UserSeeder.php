<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModule;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $moduleId = config('module.id');

            $department = Department::query()->orderBy('created_at')->first();

            if (! $department) {
                throw new \RuntimeException('UserSeeder: no department found. Seed departments first.');
            }

            $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
            $staffRole = Role::firstOrCreate(['name' => 'Staff']);

            $admin = User::factory()
                ->admin()
                ->create([
                    'primary_department_id' => $department->id,
                ]);

            UserProfile::factory()->create([
                'user_id' => $admin->id,
                'first_name' => 'System',
                'last_name' => 'Administrator',
            ]);

            $admin->assignRole($adminRole);

            UserModule::updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'module_id' => $moduleId,
                    'department_id' => $department->id,
                ],
                [
                    'is_active' => true,
                    'granted_at' => now(),
                    'revoked_at' => null,
                ]
            );

            $staffUsers = User::factory()
                ->count(5)
                ->mustChangePassword()
                ->create([
                    'primary_department_id' => $department->id,
                ]);

            foreach ($staffUsers as $staff) {
                UserProfile::factory()->create([
                    'user_id' => $staff->id,
                ]);

                $staff->assignRole($staffRole);

                UserModule::updateOrCreate(
                    [
                        'user_id' => $staff->id,
                        'module_id' => $moduleId,
                        'department_id' => $department->id,
                    ],
                    [
                        'is_active' => true,
                        'granted_at' => now(),
                        'revoked_at' => null,
                    ]
                );
            }
        });
    }
}