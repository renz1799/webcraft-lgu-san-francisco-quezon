<?php

namespace Tests\Feature;

use App\Core\Models\Department;
use App\Core\Models\Module;
use App\Core\Models\User;
use App\Core\Models\UserModule;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModuleRuntimeRoutingTest extends TestCase
{
    private const CORE_MODULE_ID = 'module-core';
    private const TASKS_MODULE_ID = 'module-tasks';
    private const GSO_MODULE_ID = 'module-gso';
    private const ITO_DEPARTMENT_ID = 'department-ito';
    private const GSO_DEPARTMENT_ID = 'department-gso';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        $this->seedModules();
        $this->registerProbeRoutes();
    }

    public function test_active_module_middleware_auto_selects_the_only_accessible_module(): void
    {
        $user = $this->createUserWithModules('user-single-tasks', ['TASKS']);

        $response = $this->actingAs($user)->get('/_test/active-module-probe');

        $response->assertOk();
        $response->assertJson([
            'module_id' => self::TASKS_MODULE_ID,
            'module_code' => 'TASKS',
            'session_module_id' => self::TASKS_MODULE_ID,
            'session_module_code' => 'TASKS',
        ]);
        $response->assertSessionHas('current_module_id', self::TASKS_MODULE_ID);
        $response->assertSessionHas('current_module_code', 'TASKS');
    }

    public function test_active_module_middleware_redirects_multi_module_users_to_selector_without_overwriting_context(): void
    {
        $user = $this->createUserWithModules('user-multi-module', ['TASKS', 'GSO']);

        $response = $this->actingAs($user)->get('/_test/active-module-probe');

        $response->assertRedirect(route('modules.index'));
        $response->assertSessionMissing('current_module_id');
        $response->assertSessionMissing('current_module_code');
    }

    public function test_active_module_middleware_uses_session_selected_module_context(): void
    {
        $user = $this->createUserWithModules('user-session-gso', ['TASKS', 'GSO']);

        $response = $this
            ->withSession([
                'current_module_id' => self::GSO_MODULE_ID,
                'current_module_code' => 'GSO',
            ])
            ->actingAs($user)
            ->get('/_test/active-module-probe');

        $response->assertOk();
        $response->assertJson([
            'module_id' => self::GSO_MODULE_ID,
            'module_code' => 'GSO',
            'session_module_id' => self::GSO_MODULE_ID,
            'session_module_code' => 'GSO',
        ]);
    }

    public function test_gso_module_routes_set_current_context_and_session(): void
    {
        $user = $this->createUserWithModules('user-gso-only', ['GSO']);

        $response = $this->actingAs($user)->get('/_test/gso-probe');

        $response->assertOk();
        $response->assertJson([
            'module_id' => self::GSO_MODULE_ID,
            'module_code' => 'GSO',
            'session_module_id' => self::GSO_MODULE_ID,
            'session_module_code' => 'GSO',
        ]);
        $response->assertSessionHas('current_module_id', self::GSO_MODULE_ID);
        $response->assertSessionHas('current_module_code', 'GSO');
    }

    public function test_gso_module_routes_reject_users_without_gso_access(): void
    {
        $user = $this->createUserWithModules('user-tasks-only', ['TASKS']);

        $this->actingAs($user)
            ->get('/_test/gso-probe')
            ->assertForbidden();
    }

    public function test_core_platform_selector_redirects_to_core_home_and_remembers_selection(): void
    {
        $user = $this->createUserWithModules('user-core-gso', ['CORE', 'GSO'], userType: 'Admin');

        $response = $this->actingAs($user)->get('/modules/core');

        $response->assertRedirect(route('access.users.index'));
        $response->assertSessionHas('current_module_id', self::CORE_MODULE_ID);
        $response->assertSessionHas('current_module_code', 'CORE');
    }

    public function test_platform_routes_bind_core_context_even_when_session_points_to_business_module(): void
    {
        config()->set('modules.platform_context_route_names', ['test.core-platform.*']);

        $user = $this->createUserWithModules('user-core-context', ['CORE', 'GSO'], userType: 'Admin');

        $response = $this
            ->withSession([
                'current_module_id' => self::GSO_MODULE_ID,
                'current_module_code' => 'GSO',
            ])
            ->actingAs($user)
            ->get('/_test/core-platform-probe');

        $response->assertOk();
        $response->assertJson([
            'module_id' => self::CORE_MODULE_ID,
            'module_code' => 'CORE',
            'session_module_id' => self::CORE_MODULE_ID,
            'session_module_code' => 'CORE',
        ]);
    }

    public function test_module_selector_open_redirects_to_canonical_gso_home_and_remembers_selection(): void
    {
        $user = $this->createUserWithModules('user-selector-gso', ['TASKS', 'GSO']);

        $response = $this->actingAs($user)->get('/modules/gso');

        $response->assertRedirect(route('gso.dashboard'));
        $response->assertSessionHas('current_module_id', self::GSO_MODULE_ID);
        $response->assertSessionHas('current_module_code', 'GSO');
    }

    public function test_authenticated_landing_redirects_single_module_users_to_their_module_home(): void
    {
        $user = $this->createUserWithModules('user-landing-gso', ['GSO']);

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('gso.dashboard'));
        $response->assertSessionHas('current_module_id', self::GSO_MODULE_ID);
        $response->assertSessionHas('current_module_code', 'GSO');
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('type')->nullable();
            $table->uuid('parent_department_id')->nullable();
            $table->uuid('head_user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->uuid('default_department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('primary_department_id')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_type')->default('Viewer');
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('module_id');
            $table->uuid('department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    private function seedModules(): void
    {
        DB::table('departments')->insert([
            [
                'id' => self::ITO_DEPARTMENT_ID,
                'code' => 'ITO',
                'name' => 'Information Technology Office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => self::GSO_DEPARTMENT_ID,
                'code' => 'GSO',
                'name' => 'General Services Office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('modules')->insert([
            [
                'id' => self::CORE_MODULE_ID,
                'code' => 'CORE',
                'name' => 'Core Platform',
                'type' => 'platform',
                'default_department_id' => self::ITO_DEPARTMENT_ID,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => self::TASKS_MODULE_ID,
                'code' => 'TASKS',
                'name' => 'Tasks',
                'type' => 'support',
                'default_department_id' => self::ITO_DEPARTMENT_ID,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => self::GSO_MODULE_ID,
                'code' => 'GSO',
                'name' => 'General Services Office',
                'type' => 'business',
                'default_department_id' => self::GSO_DEPARTMENT_ID,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function registerProbeRoutes(): void
    {
        if (! Route::has('test.active-module.probe')) {
            Route::middleware(['web', 'auth', 'password.changed', 'active_module'])
                ->get('/_test/active-module-probe', function (CurrentContext $context) {
                    return response()->json($this->contextPayload($context));
                })
                ->name('test.active-module.probe');
        }

        if (! Route::has('test.gso.probe')) {
            Route::middleware(['web', 'auth', 'password.changed', 'module:gso'])
                ->get('/_test/gso-probe', function (CurrentContext $context) {
                    return response()->json($this->contextPayload($context));
                })
                ->name('test.gso.probe');
        }

        if (! Route::has('test.core-platform.probe')) {
            Route::middleware(['web', 'auth', 'password.changed', 'active_module'])
                ->get('/_test/core-platform-probe', function (CurrentContext $context) {
                    return response()->json($this->contextPayload($context));
                })
                ->name('test.core-platform.probe');
        }
    }

    private function contextPayload(CurrentContext $context): array
    {
        return [
            'module_id' => $context->moduleId(),
            'module_code' => $context->module()?->code,
            'session_module_id' => session('current_module_id'),
            'session_module_code' => session('current_module_code'),
        ];
    }

    private function createUserWithModules(string $id, array $moduleCodes, string $userType = 'Viewer'): User
    {
        $primaryModuleCode = $moduleCodes[0] ?? 'TASKS';
        $primaryDepartmentId = $this->departmentIdForModule($primaryModuleCode);

        DB::table('users')->insert([
            'id' => $id,
            'primary_department_id' => $primaryDepartmentId,
            'username' => $id,
            'email' => "{$id}@example.com",
            'password' => 'secret',
            'user_type' => $userType,
            'is_active' => true,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($moduleCodes as $moduleCode) {
            $module = Module::query()->where('code', $moduleCode)->firstOrFail();

            DB::table('user_modules')->insert([
                'id' => "{$id}-" . strtolower($moduleCode),
                'user_id' => $id,
                'module_id' => $module->id,
                'department_id' => $this->departmentIdForModule($moduleCode),
                'is_active' => true,
                'granted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return User::query()->findOrFail($id);
    }

    private function departmentIdForModule(string $moduleCode): string
    {
        return match ($moduleCode) {
            'GSO' => self::GSO_DEPARTMENT_ID,
            default => self::ITO_DEPARTMENT_ID,
        };
    }
}
