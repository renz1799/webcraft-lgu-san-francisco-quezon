<?php

namespace Database\Seeders;

use App\Core\Models\ModelHasRole;
use App\Core\Models\Module;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Models\UserModule;
use App\Core\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $moduleCodes = ['GSO'];

            $modules = Module::query()
                ->whereIn('code', $moduleCodes)
                ->get()
                ->keyBy(fn (Module $module) => strtoupper((string) $module->code));

            if ($modules->count() !== count($moduleCodes)) {
                throw new \RuntimeException('UserSeeder: required modules not found. Run ModuleSeeder first.');
            }

            $primaryModule = $modules->first();
            $primaryDepartmentId = (string) ($primaryModule?->default_department_id ?? '');

            if ($primaryDepartmentId === '') {
                throw new \RuntimeException('UserSeeder: default department not found for seeded modules.');
            }

            $rolesByModule = $modules->mapWithKeys(function (Module $module) {
                $adminRole = Role::query()->firstOrCreate(
                    [
                        'module_id' => $module->id,
                        'name' => 'Administrator',
                        'guard_name' => 'web',
                    ],
                    [
                        'id' => (string) Str::uuid(),
                    ]
                );

                $staffRole = Role::query()->firstOrCreate(
                    [
                        'module_id' => $module->id,
                        'name' => 'Staff',
                        'guard_name' => 'web',
                    ],
                    [
                        'id' => (string) Str::uuid(),
                    ]
                );

                return [
                    strtoupper((string) $module->code) => [
                        'module' => $module,
                        'admin_role' => $adminRole,
                        'staff_role' => $staffRole,
                    ],
                ];
            });

            $admin = User::query()->updateOrCreate(
                [
                    'email' => 'webcraftdev.ph@gmail.com',
                ],
                [
                    'username' => 'admin',
                    'password' => Hash::make('password'),
                    'user_type' => 'Admin',
                    'is_active' => true,
                    'must_change_password' => false,
                    'primary_department_id' => $primaryDepartmentId,
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

            $this->syncUserAcrossModules($admin, $rolesByModule, 'admin_role');

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
                        'primary_department_id' => $primaryDepartmentId,
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

                $this->syncUserAcrossModules($staff, $rolesByModule, 'staff_role');
            }
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int|string, array{module: Module, admin_role: Role, staff_role: Role}>  $rolesByModule
     */
    private function syncUserAcrossModules(User $user, $rolesByModule, string $roleKey): void
    {
        foreach ($rolesByModule as $entry) {
            /** @var Module $module */
            $module = $entry['module'];
            /** @var Role $role */
            $role = $entry[$roleKey];

            UserModule::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'module_id' => $module->id,
                ],
                [
                    'department_id' => $module->default_department_id,
                    'is_active' => true,
                    'granted_at' => now(),
                    'revoked_at' => null,
                ]
            );

            ModelHasRole::query()->updateOrCreate(
                [
                    'module_id' => $module->id,
                    'role_id' => $role->id,
                    'model_type' => User::class,
                    'model_id' => $user->id,
                ],
                []
            );
        }
    }
}
