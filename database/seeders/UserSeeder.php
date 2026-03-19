<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserModule;
use App\Models\UserProfile;
use App\Support\CurrentContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $context = app(CurrentContext::class);

            $moduleId = $context->moduleId();
            $departmentId = $context->defaultDepartmentId();

            if (! $moduleId) {
                throw new \RuntimeException('UserSeeder: current module not found. Run ModuleSeeder first.');
            }

            if (! $departmentId) {
                throw new \RuntimeException('UserSeeder: default department not found. Run DepartmentSeeder first.');
            }

            $adminRole = Role::firstOrCreate(['name' => 'Administrator']);
            $staffRole = Role::firstOrCreate(['name' => 'Staff']);

            $admin = User::factory()
                ->admin()
                ->create([
                    'primary_department_id' => $departmentId,
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
                    'department_id' => $departmentId,
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
                    'primary_department_id' => $departmentId,
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
                        'department_id' => $departmentId,
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