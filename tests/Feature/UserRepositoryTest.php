<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Repositories\Eloquent\EloquentUserRepository;
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

        parent::tearDown();
    }

    public function test_get_user_ids_by_roles_returns_existing_role_users_and_warns_for_missing_roles(): void
    {
        Log::spy();

        $repository = new EloquentUserRepository();
        $adminRole = Role::create(['name' => 'Administrator', 'guard_name' => 'web']);
        $staffRole = Role::create(['name' => 'Staff', 'guard_name' => 'web']);

        $adminUser = $this->createUser('admin-user', 'admin@example.com');
        $staffUser = $this->createUser('staff-user', 'staff@example.com');

        $this->attachRole($adminUser, $adminRole);
        $this->attachRole($staffUser, $staffRole);

        $ids = $repository->getUserIdsByRoles([' Administrator ', 'Inspector', 'Staff', 'Staff']);

        $expectedIds = [(string) $adminUser->id, (string) $staffUser->id];
        sort($expectedIds);
        $actualIds = $ids;
        sort($actualIds);

        $this->assertSame($expectedIds, $actualIds);

        Log::shouldHaveReceived('warning')->once()->with(
            'User role lookup skipped missing roles.',
            Mockery::on(function (array $context): bool {
                return $context['guard_name'] === 'web'
                    && $context['requested_roles'] === ['Administrator', 'Inspector', 'Staff']
                    && $context['missing_roles'] === ['Inspector'];
            })
        );
    }

    public function test_get_user_ids_by_roles_returns_empty_array_when_no_requested_roles_exist(): void
    {
        Log::spy();

        $repository = new EloquentUserRepository();

        $ids = $repository->getUserIdsByRoles(['Inspector', 'Approver']);

        $this->assertSame([], $ids);

        Log::shouldHaveReceived('warning')->once()->with(
            'User role lookup skipped missing roles.',
            Mockery::on(function (array $context): bool {
                return $context['guard_name'] === 'web'
                    && $context['requested_roles'] === ['Inspector', 'Approver']
                    && $context['missing_roles'] === ['Inspector', 'Approver'];
            })
        );
    }

    public function test_get_user_ids_by_roles_returns_empty_array_without_logging_for_empty_input(): void
    {
        Log::spy();

        $repository = new EloquentUserRepository();

        $this->assertSame([], $repository->getUserIdsByRoles(['', '   ']));

        Log::shouldNotHaveReceived('warning');
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
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'guard_name', 'deleted_at'], 'roles_name_guard_deleted_unique');
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
        });
    }

    private function createUser(string $username, string $email): User
    {
        return User::create([
            'username' => $username,
            'email' => $email,
            'password' => 'secret',
            'user_type' => 'Viewer',
            'is_active' => true,
        ]);
    }

    private function attachRole(User $user, Role $role): void
    {
        DB::table('model_has_roles')->insert([
            'role_id' => (string) $role->id,
            'model_type' => User::class,
            'model_id' => (string) $user->id,
        ]);
    }
}