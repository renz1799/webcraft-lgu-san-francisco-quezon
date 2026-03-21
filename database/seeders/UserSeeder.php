<?php

namespace Database\Seeders;

use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Models\UserModule;
use App\Core\Models\UserProfile;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $context = app(CurrentContext::class);
            $roleAssignments = app(ModuleRoleAssignmentServiceInterface::class);

            $moduleId = $context->moduleId();
            $departmentId = $context->defaultDepartmentId();

            if (! $moduleId) {
                throw new \RuntimeException('UserSeeder: current module not found. Run ModuleSeeder first.');
            }

            if (! $departmentId) {
                throw new \RuntimeException('UserSeeder: default department not found. Run DepartmentSeeder first.');
            }

            $adminRole = Role::query()->firstOrCreate(
                [
                    'module_id' => $moduleId,
                    'name' => 'Administrator',
                    'guard_name' => 'web',
                ],
                [
                    'id' => (string) Str::uuid(),
                ]
            );

            $staffRole = Role::query()->firstOrCreate(
                [
                    'module_id' => $moduleId,
                    'name' => 'Staff',
                    'guard_name' => 'web',
                ],
                [
                    'id' => (string) Str::uuid(),
                ]
            );

            $admin = User::query()->updateOrCreate(
                [
                    'email' => 'admin@webcraft.ph',
                ],
                [
                    'username' => 'admin',
                    'password' => Hash::make('password'),
                    'user_type' => 'Admin',
                    'is_active' => true,
                    'must_change_password' => false,
                    'primary_department_id' => $departmentId,
                ]
            );

            UserProfile::query()->updateOrCreate(
                [
                    'user_id' => $admin->id,
                ],
                [
                    'first_name' => 'System',
                    'last_name' => 'Administrator',
                ]
            );

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

            $roleAssignments->assign($admin, $adminRole);

            for ($index = 1; $index <= 5; $index++) {
                $staff = User::query()->updateOrCreate(
                    [
                        'email' => "staff{$index}@webcraft.ph",
                    ],
                    [
                        'username' => "staff{$index}",
                        'password' => Hash::make('password'),
                        'user_type' => 'Viewer',
                        'is_active' => true,
                        'must_change_password' => true,
                        'primary_department_id' => $departmentId,
                    ]
                );

                UserProfile::query()->updateOrCreate(
                    [
                        'user_id' => $staff->id,
                    ],
                    [
                        'first_name' => 'Sample',
                        'last_name' => "Staff {$index}",
                    ]
                );

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

                $roleAssignments->assign($staff, $staffRole);
            }
        });
    }
}
