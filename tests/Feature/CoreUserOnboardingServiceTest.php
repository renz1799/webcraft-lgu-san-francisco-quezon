<?php

namespace Tests\Feature;

use App\Core\Data\Users\CoreUserOnboardingData;
use App\Core\Models\User;
use App\Core\Services\Access\CoreUserOnboardingService;
use App\Core\Services\Access\ModuleDepartmentResolver;
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
use Mockery;
use Tests\TestCase;

class CoreUserOnboardingServiceTest extends TestCase
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
        Config::set('module.id', 'module-core');
        Config::set('app.name', 'Webcraft LGU Platform');
        Config::set('mail.from.name', 'Webcraft LGU Platform');
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_core_onboard_creates_platform_identity_and_assigns_the_selected_module(): void
    {
        Notification::fake();

        $audit = Mockery::spy(AuditLogServiceInterface::class);
        $service = $this->makeService($audit);

        $result = $service->onboard(
            actor: User::query()->create([
                'id' => 'actor-core-admin',
                'primary_department_id' => 'department-ito',
                'username' => 'core.admin',
                'email' => 'core.admin@example.com',
                'password' => Hash::make('secret-password'),
                'user_type' => 'Administrator',
                'is_active' => true,
                'must_change_password' => false,
            ]),
            data: CoreUserOnboardingData::fromArray([
                'first_name' => 'Ana',
                'last_name' => 'Rivera',
                'email' => 'ana.rivera@example.com',
                'module_id' => 'module-gso',
                'department_id' => 'department-gso',
                'role' => 'Staff',
                'is_active' => false,
            ]),
        );

        $user = User::query()->where('email', 'ana.rivera@example.com')->firstOrFail();

        $this->assertSame('created', $result['status']);
        $this->assertSame('department-gso', $user->primary_department_id);
        $this->assertTrue((bool) $user->must_change_password);

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
        $audit->shouldHaveReceived('record')->withArgs(fn (string $action) => $action === 'user.core_onboarding.completed')->once();
        $audit->shouldHaveReceived('record')->withArgs(fn (string $action) => $action === 'auth.invitation.sent')->once();
    }

    public function test_core_onboard_reuses_existing_identity_and_attaches_selected_module_membership(): void
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
            'id' => 'user-existing-core',
            'user_id' => $user->id,
            'module_id' => 'module-core',
            'department_id' => 'department-ito',
            'is_active' => true,
            'granted_at' => now(),
            'revoked_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $audit = Mockery::spy(AuditLogServiceInterface::class);
        $service = $this->makeService($audit);

        $result = $service->onboard(
            actor: $user,
            data: CoreUserOnboardingData::fromArray([
                'first_name' => 'Ignored',
                'last_name' => 'Name',
                'email' => 'existing.staff@example.com',
                'module_id' => 'module-tasks',
                'department_id' => 'department-ito',
                'role' => 'Staff',
                'is_active' => true,
            ]),
        );

        $fresh = $user->fresh();

        $this->assertSame('attached', $result['status']);
        $this->assertSame(1, User::query()->where('email', 'existing.staff@example.com')->count());
        $this->assertSame('department-ito', $fresh->primary_department_id);

        $this->assertDatabaseHas('user_modules', [
            'user_id' => $user->id,
            'module_id' => 'module-tasks',
            'department_id' => 'department-ito',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('model_has_roles', [
            'module_id' => 'module-tasks',
            'role_id' => 'role-tasks-staff',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        Notification::assertSentTo($fresh, ModuleAccessGrantedNotification::class, function (ModuleAccessGrantedNotification $notification) use ($fresh) {
            $mail = $notification->toMail($fresh);

            $this->assertSame('You were granted access to Tasks', $mail->subject);
            $this->assertSame('Login to System', $mail->actionText);

            return true;
        });
    }

    public function test_core_onboard_allows_assigning_administrator_role_for_the_selected_module(): void
    {
        Notification::fake();

        $audit = Mockery::spy(AuditLogServiceInterface::class);
        $service = $this->makeService($audit);

        $result = $service->onboard(
            actor: User::query()->create([
                'id' => 'actor-core-admin-2',
                'primary_department_id' => 'department-ito',
                'username' => 'core.admin.two',
                'email' => 'core.admin.two@example.com',
                'password' => Hash::make('secret-password'),
                'user_type' => 'Administrator',
                'is_active' => true,
                'must_change_password' => false,
            ]),
            data: CoreUserOnboardingData::fromArray([
                'first_name' => 'Task',
                'last_name' => 'Admin',
                'email' => 'tasks.admin@example.com',
                'module_id' => 'module-tasks',
                'department_id' => 'department-ito',
                'role' => 'Administrator',
                'is_active' => true,
            ]),
        );

        $user = User::query()->where('email', 'tasks.admin@example.com')->firstOrFail();

        $this->assertSame('created', $result['status']);
        $this->assertDatabaseHas('model_has_roles', [
            'module_id' => 'module-tasks',
            'role_id' => 'role-tasks-admin',
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_core_create_data_exposes_module_hints_and_department_mapping(): void
    {
        $service = $this->makeService(Mockery::spy(AuditLogServiceInterface::class));

        $data = $service->getCreateData();

        $this->assertSame('core', $data['onboardingMode']);
        $this->assertSame('module-core', $data['selectedModuleId']);
        $this->assertSame('department-gso', $data['departmentsByModule']['module-gso'][0]['value']);
        $this->assertSame('General Services Office', $data['moduleHints']['module-gso']['default_department_label']);
        $this->assertContains('Administrator', array_column($data['rolesByModule']['module-gso'], 'name'));
        $this->assertContains('Staff', array_column($data['rolesByModule']['module-gso'], 'name'));
    }

    public function test_core_onboarding_view_embeds_raw_json_config_for_the_frontend(): void
    {
        $service = $this->makeService(Mockery::spy(AuditLogServiceInterface::class));

        $html = view('access.users.create', $service->getCreateData())->render();

        $this->assertStringContainsString('id="userOnboardingConfig"', $html);
        $this->assertStringContainsString('"departmentsByModule"', $html);
        $this->assertStringContainsString('"module-gso"', $html);
        $this->assertStringNotContainsString('&quot;', $html);
        $this->assertStringNotContainsString('JSON.parse(', $html);
    }

    private function makeService(AuditLogServiceInterface $audit): CoreUserOnboardingService
    {
        $context = new CurrentContext();

        return new CoreUserOnboardingService(
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

        Schema::create('app_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id')->nullable();
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
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
                'id' => 'role-core-admin',
                'module_id' => 'module-core',
                'name' => 'Administrator',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'role-core-staff',
                'module_id' => 'module-core',
                'name' => 'Staff',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
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
