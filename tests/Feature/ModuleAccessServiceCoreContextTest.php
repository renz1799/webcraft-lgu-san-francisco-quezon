<?php

namespace Tests\Feature;

use App\Core\Models\Module;
use App\Core\Models\User;
use App\Core\Services\Access\ModuleAccessService;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ModuleAccessServiceCoreContextTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        $this->seedModules();
        $this->registerRoutes();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_accessible_modules_for_admin_users_includes_core_platform_context(): void
    {
        $user = $this->createUser('admin-user', 'Admin');
        $this->attachUserModule($user, 'module-gso');
        $this->attachUserModule($user, 'module-tasks');

        $service = $this->makeService();

        $modules = $service->accessibleModulesForUser($user);

        $this->assertSame(['CORE', 'GSO', 'TASKS'], $modules->pluck('code')->all());
        $this->assertSame(['CORE', 'GSO'], $service->switchableModulesForUser($user)->pluck('code')->all());
    }

    public function test_has_active_module_access_grants_core_to_platform_admin_users(): void
    {
        $user = $this->createUser('admin-user', 'Admin');
        $service = $this->makeService();

        $this->assertTrue($service->hasActiveModuleAccess($user, 'module-core'));
    }

    public function test_viewer_users_do_not_receive_core_platform_context_without_explicit_access(): void
    {
        $user = $this->createUser('viewer-user', 'Viewer');
        $this->attachUserModule($user, 'module-gso');

        $service = $this->makeService();

        $this->assertSame(['GSO'], $service->accessibleModulesForUser($user)->pluck('code')->all());
        $this->assertFalse($service->hasActiveModuleAccess($user, 'module-core'));
    }

    public function test_home_path_for_core_platform_uses_global_core_route(): void
    {
        $service = $this->makeService();

        $this->assertSame(route('access.users.index'), $service->homePathForModule('CORE'));
    }

    public function test_legacy_tasks_shared_capability_still_resolves_to_tasks_home_route(): void
    {
        $service = $this->makeService();

        $this->assertSame(route('tasks.index'), $service->homePathForModule('TASKS'));
    }

    private function makeService(): ModuleAccessService
    {
        $resolver = Mockery::mock(ModuleDepartmentResolverInterface::class);
        $resolver->shouldIgnoreMissing();

        return new ModuleAccessService($resolver);
    }

    private function createSchema(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->nullable();
            $table->uuid('default_department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('user_type')->default('Viewer');
            $table->boolean('is_active')->default(true);
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
        DB::table('modules')->insert([
            [
                'id' => 'module-core',
                'code' => 'CORE',
                'name' => 'Core Platform',
                'type' => 'platform',
                'default_department_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'module-tasks',
                'code' => 'TASKS',
                'name' => 'Tasks',
                'type' => 'support',
                'default_department_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'module-gso',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'type' => 'business',
                'default_department_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    private function registerRoutes(): void
    {
        if (! Route::has('access.users.index')) {
            Route::get('/users', fn () => 'ok')->name('access.users.index');
        }

        if (! Route::has('tasks.index')) {
            Route::get('/tasks', fn () => 'ok')->name('tasks.index');
        }
    }

    private function createUser(string $id, string $userType): User
    {
        return User::query()->create([
            'id' => $id,
            'username' => $id,
            'email' => "{$id}@example.com",
            'password' => 'secret',
            'user_type' => $userType,
            'is_active' => true,
        ]);
    }

    private function attachUserModule(User $user, string $moduleId): void
    {
        DB::table('user_modules')->insert([
            'id' => "{$user->id}-{$moduleId}",
            'user_id' => (string) $user->id,
            'module_id' => $moduleId,
            'department_id' => null,
            'is_active' => true,
            'granted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
