<?php

namespace Tests\Feature;

use App\Core\Data\Users\ModuleUserOnboardingData;
use App\Core\Models\User;
use App\Core\Services\Access\ModuleDepartmentResolver;
use App\Core\Services\Access\ModuleUserOnboardingService;
use App\Core\Services\Access\OnboardingCredentialNotificationService;
use App\Core\Services\Access\RoleAssignments\ModuleRoleAssignmentService;
use App\Core\Notifications\Auth\ModuleAccessGrantedNotification;
use App\Core\Notifications\Auth\UserInvitationNotification;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class ModuleUserOnboardingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('session.driver', 'array');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        $this->seedContextData();

        Config::set('auth.providers.users.model', User::class);
        Config::set('module.id', 'module-gso');
        Config::set('app.name', 'Webcraft LGU Platform');
        Config::set('mail.from.name', 'Webcraft LGU Platform');
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_onboard_creates_new_platform_identity_and_allows_inactive_module_assignment(): void
    {
        Notification::fake();

        $audit = Mockery::spy(AuditLogServiceInterface::class);
        $service = $this->makeService($audit);

        $result = $service->onboard(ModuleUserOnboardingData::fromArray([
            'first_name' => 'Maria',
            'middle_name' => 'Santos',
            'last_name' => 'Cruz',
            'name_extension' => 'Jr.',
            'email' => 'maria.cruz@example.com',
            'role' => 'Staff',
            'department_id' => 'department-gso',
            'is_active' => false,
        ]));

        $user = User::query()->where('email', 'maria.cruz@example.com')->firstOrFail();

        $this->assertSame('created', $result['status']);
        $this->assertSame('Viewer', $user->user_type);
        $this->assertTrue((bool) $user->must_change_password);
        $this->assertSame('department-gso', $user->primary_department_id);
        $this->assertTrue(Hash::check($user->email === 'maria.cruz@example.com' ? 'not-the-password' : '', $user->password) === false);

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'first_name' => 'Maria',
            'middle_name' => 'Santos',
            'last_name' => 'Cruz',
            'name_extension' => 'Jr.',
        ]);

        $this->assertDatabaseHas('user_modules', [
            'user_id' => $user->id,
            'module_id' => 'module-gso',
            'department_id' => 'department-gso',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('model_has_roles', [
            'module_id' => 'module-gso',
            'role_id' => 'role-gso-staff',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        Notification::assertSentTo($user, UserInvitationNotification::class, function (UserInvitationNotification $notification) use ($user) {
            $mail = $notification->toMail($user);

            $this->assertSame("You're invited to Webcraft LGU Platform", $mail->subject);
            $this->assertSame('Set Your Password', $mail->actionText);
            $this->assertStringContainsString('flow=invitation', (string) $mail->actionUrl);

            return true;
        });
        $audit->shouldHaveReceived('record')->withArgs(fn (string $action) => $action === 'user.module_onboarding.completed')->once();
        $audit->shouldHaveReceived('record')->withArgs(fn (string $action) => $action === 'auth.invitation.sent')->once();
    }

    public function test_onboard_reuses_existing_platform_identity_and_attaches_module_membership(): void
    {
        Notification::fake();

        $user = User::query()->create([
            'id' => 'user-existing',
            'primary_department_id' => 'department-ito',
            'username' => 'existing.staff',
            'email' => 'existing.staff@example.com',
            'password' => Hash::make('secret-password'),
            'user_type' => 'Viewer',
            'is_active' => true,
            'must_change_password' => false,
        ]);

        DB::table('user_modules')->insert([
            'id' => 'user-existing-tasks',
            'user_id' => $user->id,
            'module_id' => 'module-tasks',
            'department_id' => 'department-ito',
            'is_active' => true,
            'granted_at' => now(),
            'revoked_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $audit = Mockery::spy(AuditLogServiceInterface::class);
        $service = $this->makeService($audit);

        $result = $service->onboard(ModuleUserOnboardingData::fromArray([
            'first_name' => 'Should',
            'last_name' => 'Ignore',
            'email' => 'existing.staff@example.com',
            'role' => 'Staff',
            'department_id' => 'department-gso',
            'is_active' => true,
        ]));

        $fresh = $user->fresh();

        $this->assertSame('attached', $result['status']);
        $this->assertSame('department-ito', $fresh->primary_department_id);
        $this->assertSame(1, User::query()->where('email', 'existing.staff@example.com')->count());
        $this->assertDatabaseHas('user_modules', [
            'user_id' => $user->id,
            'module_id' => 'module-gso',
            'department_id' => 'department-gso',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('model_has_roles', [
            'module_id' => 'module-gso',
            'role_id' => 'role-gso-staff',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        Notification::assertSentTo($fresh, ModuleAccessGrantedNotification::class, function (ModuleAccessGrantedNotification $notification) use ($fresh) {
            $mail = $notification->toMail($fresh);

            $this->assertSame('You were granted access to General Services Office', $mail->subject);
            $this->assertSame('Login to System', $mail->actionText);

            return true;
        });
    }

    public function test_onboard_sends_invitation_for_existing_users_that_still_need_password_setup(): void
    {
        Notification::fake();

        $user = User::query()->create([
            'id' => 'user-invited',
            'primary_department_id' => 'department-ito',
            'username' => 'invited.staff',
            'email' => 'invited.staff@example.com',
            'password' => Hash::make('secret-password'),
            'user_type' => 'Viewer',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $service = $this->makeService(Mockery::spy(AuditLogServiceInterface::class));

        $service->onboard(ModuleUserOnboardingData::fromArray([
            'first_name' => 'Invited',
            'last_name' => 'Staff',
            'email' => 'invited.staff@example.com',
            'role' => 'Staff',
            'department_id' => 'department-gso',
            'is_active' => true,
        ]));

        Notification::assertSentTo($user->fresh(), UserInvitationNotification::class);
    }

    public function test_onboard_allows_assigning_administrator_role_when_it_exists_in_the_module(): void
    {
        Notification::fake();

        $audit = Mockery::spy(AuditLogServiceInterface::class);
        $service = $this->makeService($audit);

        $result = $service->onboard(ModuleUserOnboardingData::fromArray([
            'first_name' => 'Gso',
            'last_name' => 'Lead',
            'email' => 'gso.admin@example.com',
            'role' => 'Administrator',
            'department_id' => 'department-gso',
            'is_active' => true,
        ]));

        $user = User::query()->where('email', 'gso.admin@example.com')->firstOrFail();

        $this->assertSame('created', $result['status']);
        $this->assertDatabaseHas('model_has_roles', [
            'module_id' => 'module-gso',
            'role_id' => 'role-gso-admin',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_onboard_forces_the_module_default_department_even_if_input_is_tampered(): void
    {
        Notification::fake();

        $audit = Mockery::spy(AuditLogServiceInterface::class);
        $service = $this->makeService($audit);

        $service->onboard(ModuleUserOnboardingData::fromArray([
            'first_name' => 'Tampered',
            'last_name' => 'Department',
            'email' => 'tampered.department@example.com',
            'role' => 'Staff',
            'department_id' => 'department-ito',
            'is_active' => true,
        ]));

        $user = User::query()->where('email', 'tampered.department@example.com')->firstOrFail();

        $this->assertDatabaseHas('user_modules', [
            'user_id' => $user->id,
            'module_id' => 'module-gso',
            'department_id' => 'department-gso',
            'is_active' => true,
        ]);
    }

    public function test_onboard_blocks_archived_platform_identities(): void
    {
        Notification::fake();

        $user = User::query()->create([
            'id' => 'user-archived',
            'primary_department_id' => 'department-ito',
            'username' => 'archived.staff',
            'email' => 'archived.staff@example.com',
            'password' => Hash::make('secret-password'),
            'user_type' => 'Viewer',
            'is_active' => true,
        ]);
        $user->delete();

        $service = $this->makeService(Mockery::spy(AuditLogServiceInterface::class));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('archived in Core Platform');

        $service->onboard(ModuleUserOnboardingData::fromArray([
            'first_name' => 'Archived',
            'last_name' => 'Staff',
            'email' => 'archived.staff@example.com',
            'role' => 'Staff',
            'is_active' => true,
        ]));
    }

    public function test_onboard_blocks_globally_inactive_platform_identities(): void
    {
        Notification::fake();

        User::query()->create([
            'id' => 'user-inactive',
            'primary_department_id' => 'department-ito',
            'username' => 'inactive.staff',
            'email' => 'inactive.staff@example.com',
            'password' => Hash::make('secret-password'),
            'user_type' => 'Viewer',
            'is_active' => false,
        ]);

        $service = $this->makeService(Mockery::spy(AuditLogServiceInterface::class));

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('inactive in Core Platform');

        $service->onboard(ModuleUserOnboardingData::fromArray([
            'first_name' => 'Inactive',
            'last_name' => 'Staff',
            'email' => 'inactive.staff@example.com',
            'role' => 'Staff',
            'is_active' => true,
        ]));
    }

    public function test_onboard_returns_noop_when_assignment_already_matches_requested_state(): void
    {
        Notification::fake();

        $user = User::query()->create([
            'id' => 'user-noop',
            'primary_department_id' => 'department-gso',
            'username' => 'noop.staff',
            'email' => 'noop.staff@example.com',
            'password' => Hash::make('secret-password'),
            'user_type' => 'Viewer',
            'is_active' => true,
        ]);

        DB::table('user_profiles')->insert([
            'id' => 'profile-noop',
            'user_id' => $user->id,
            'first_name' => 'Noop',
            'last_name' => 'Staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_modules')->insert([
            'id' => 'user-noop-gso',
            'user_id' => $user->id,
            'module_id' => 'module-gso',
            'department_id' => 'department-gso',
            'is_active' => true,
            'granted_at' => now(),
            'revoked_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('model_has_roles')->insert([
            'module_id' => 'module-gso',
            'role_id' => 'role-gso-staff',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $audit = Mockery::spy(AuditLogServiceInterface::class);
        $service = $this->makeService($audit);

        $result = $service->onboard(ModuleUserOnboardingData::fromArray([
            'first_name' => 'Noop',
            'last_name' => 'Staff',
            'email' => 'noop.staff@example.com',
            'role' => 'Staff',
            'department_id' => 'department-gso',
            'is_active' => true,
        ]));

        $this->assertSame('noop', $result['status']);
        $this->assertStringContainsString('already assigned', $result['message']);
        Notification::assertNothingSent();
        $audit->shouldNotHaveReceived('record');
    }

    private function makeService(AuditLogServiceInterface $audit): ModuleUserOnboardingService
    {
        $context = new CurrentContext();

        return new ModuleUserOnboardingService(
            context: $context,
            moduleDepartments: new ModuleDepartmentResolver($context),
            credentialNotifications: new OnboardingCredentialNotificationService(),
            roleAssignments: new ModuleRoleAssignmentService($context),
            audit: $audit,
        );
    }

    private function createSchema(): void
    {
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
            $table->uuid('default_department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('primary_department_id')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('user_type')->default('Viewer');
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name_extension')->nullable();
            $table->timestamps();
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

        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('name');
            $table->string('page')->nullable();
            $table->string('guard_name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->uuid('module_id');
            $table->uuid('role_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->primary(['module_id', 'role_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->uuid('module_id');
            $table->uuid('permission_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->primary(['module_id', 'permission_id', 'model_id', 'model_type']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    private function seedContextData(): void
    {
        DB::table('departments')->insert([
            [
                'id' => 'department-ito',
                'code' => 'ITO',
                'name' => 'Information Technology Office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'department-gso',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('modules')->insert([
            [
                'id' => 'module-core',
                'code' => 'CORE',
                'name' => 'Core Platform',
                'type' => 'platform',
                'default_department_id' => 'department-ito',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'module-tasks',
                'code' => 'TASKS',
                'name' => 'Tasks',
                'type' => 'support',
                'default_department_id' => 'department-ito',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'module-gso',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'type' => 'business',
                'default_department_id' => 'department-gso',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('roles')->insert([
            [
                'id' => 'role-gso-admin',
                'module_id' => 'module-gso',
                'name' => 'Administrator',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'role-gso-staff',
                'module_id' => 'module-gso',
                'name' => 'Staff',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'role-tasks-admin',
                'module_id' => 'module-tasks',
                'name' => 'Administrator',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'role-tasks-staff',
                'module_id' => 'module-tasks',
                'name' => 'Staff',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }
}
