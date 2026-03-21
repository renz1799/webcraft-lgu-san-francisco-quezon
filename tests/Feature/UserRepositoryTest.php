<?php

namespace Tests\Feature;

use App\Core\Builders\Contracts\User\UserDatatableActionBuilderInterface;
use App\Core\Builders\Contracts\User\UserDatatableRowBuilderInterface;
use App\Core\Builders\User\UserDatatableRowBuilder;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Repositories\Eloquent\EloquentUserRepository;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Mockery::close();

        parent::tearDown();
    }

    public function test_get_user_ids_by_roles_returns_existing_role_users_and_warns_for_missing_roles(): void
    {
        Log::spy();

        $repository = $this->makeRepository('module-1');
        $adminRole = Role::query()->create(['id' => 'role-1', 'module_id' => 'module-1', 'name' => 'Administrator', 'guard_name' => 'web']);
        $staffRole = Role::query()->create(['id' => 'role-2', 'module_id' => 'module-1', 'name' => 'Staff', 'guard_name' => 'web']);
        Role::query()->create(['id' => 'role-3', 'module_id' => 'module-2', 'name' => 'Staff', 'guard_name' => 'web']);

        $adminUser = $this->createUser('admin-user', 'admin@example.com');
        $staffUser = $this->createUser('staff-user', 'staff@example.com');

        $this->attachRole($adminUser, $adminRole, 'module-1');
        $this->attachRole($staffUser, $staffRole, 'module-1');

        $ids = $repository->getUserIdsByRoles([' Administrator ', 'Inspector', 'Staff', 'Staff']);

        $expectedIds = [(string) $adminUser->id, (string) $staffUser->id];
        sort($expectedIds);
        $actualIds = $ids;
        sort($actualIds);

        $this->assertSame($expectedIds, $actualIds);

        Log::shouldHaveReceived('warning')->once()->with(
            'User role lookup skipped missing roles.',
            Mockery::on(function (array $context): bool {
                return $context['module_id'] === 'module-1'
                    && $context['guard_name'] === 'web'
                    && $context['requested_roles'] === ['Administrator', 'Inspector', 'Staff']
                    && $context['missing_roles'] === ['Inspector'];
            })
        );
    }

    public function test_get_user_ids_by_roles_returns_empty_array_when_no_requested_roles_exist(): void
    {
        Log::spy();

        $repository = $this->makeRepository('module-1');

        $ids = $repository->getUserIdsByRoles(['Inspector', 'Approver']);

        $this->assertSame([], $ids);

        Log::shouldHaveReceived('warning')->once()->with(
            'User role lookup skipped missing roles.',
            Mockery::on(function (array $context): bool {
                return $context['module_id'] === 'module-1'
                    && $context['guard_name'] === 'web'
                    && $context['requested_roles'] === ['Inspector', 'Approver']
                    && $context['missing_roles'] === ['Inspector', 'Approver'];
            })
        );
    }

    public function test_get_user_ids_by_roles_returns_empty_array_without_logging_for_empty_input(): void
    {
        Log::spy();

        $repository = $this->makeRepository('module-1');

        $this->assertSame([], $repository->getUserIdsByRoles(['', '   ']));

        Log::shouldNotHaveReceived('warning');
    }

    public function test_datatable_uses_current_module_role_for_role_column(): void
    {
        $repository = $this->makeRepository('module-1', useRealRowBuilder: true);

        $user = $this->createUser('staff-user', 'staff@example.com');
        $moduleRole = Role::query()->create(['id' => 'role-1', 'module_id' => 'module-1', 'name' => 'Staff', 'guard_name' => 'web']);
        $otherRole = Role::query()->create(['id' => 'role-2', 'module_id' => 'module-2', 'name' => 'Inspector', 'guard_name' => 'web']);

        $this->attachRole($user, $otherRole, 'module-2');
        $this->attachRole($user, $moduleRole, 'module-1');

        $result = $repository->datatable([], 1, 15);

        $this->assertSame('Staff', $result['data'][0]['role']);
    }

    private function makeRepository(string $moduleId, bool $useRealRowBuilder = false): EloquentUserRepository
    {
        $rowBuilder = $useRealRowBuilder
            ? new UserDatatableRowBuilder()
            : Mockery::mock(UserDatatableRowBuilderInterface::class);

        if (! $useRealRowBuilder) {
            $rowBuilder->shouldIgnoreMissing();
        }

        $actionBuilder = Mockery::mock(UserDatatableActionBuilderInterface::class);
        $actionBuilder->shouldReceive('build')->andReturn([]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->andReturn($moduleId);

        return new EloquentUserRepository($rowBuilder, $actionBuilder, $context);
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_type')->default('Viewer');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['module_id', 'name', 'guard_name', 'deleted_at'], 'roles_module_name_guard_deleted_unique');
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->uuid('module_id');
            $table->uuid('role_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->index(['model_id', 'model_type', 'module_id'], 'model_has_roles_model_id_type_module_index');
            $table->primary(['module_id', 'role_id', 'model_id', 'model_type'], 'model_has_roles_module_role_model_type_primary');
        });
    }

    private function createUser(string $username, string $email): User
    {
        return User::query()->create([
            'username' => $username,
            'email' => $email,
            'password' => 'secret',
            'user_type' => 'Viewer',
            'is_active' => true,
        ]);
    }

    private function attachRole(User $user, Role $role, string $moduleId): void
    {
        DB::table('model_has_roles')->insert([
            'module_id' => $moduleId,
            'role_id' => (string) $role->id,
            'model_type' => User::class,
            'model_id' => (string) $user->id,
        ]);
    }
}
