<?php

namespace Tests\Feature;

use App\Core\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetFlowTest extends TestCase
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
        $this->seedCoreContext();

        Config::set('auth.providers.users.model', User::class);
    }

    public function test_forgot_password_page_is_accessible_to_guests(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
        $response->assertSee('Forgot Password');
    }

    public function test_password_reset_link_can_be_requested_without_disclosing_unknown_users(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), [
            'email' => 'unknown@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'If an account with that email exists, a password reset link has been sent.');
        Notification::assertNothingSent();
    }

    public function test_password_reset_link_is_sent_to_existing_user(): void
    {
        Notification::fake();

        $user = $this->createUser([
            'email' => 'staff@example.com',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'If an account with that email exists, a password reset link has been sent.');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_can_be_reset_from_core_route(): void
    {
        $user = $this->createUser([
            'email' => 'reset@example.com',
            'must_change_password' => true,
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'Your password has been reset. You can sign in now.');

        $fresh = $user->fresh();

        $this->assertNotNull($fresh);
        $this->assertTrue(Hash::check('new-secure-password', $fresh->password));
        $this->assertFalse((bool) $fresh->must_change_password);
    }

    private function createSchema(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->default('platform');
            $table->uuid('default_department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_type')->default('Viewer');
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->uuid('primary_department_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('user_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name_extension')->nullable();
            $table->string('full_name')->nullable();
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

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('actor_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('action');
            $table->string('message')->nullable();
            $table->string('request_method', 10)->nullable();
            $table->text('request_url')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('changes_old')->nullable();
            $table->json('changes_new')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id')->nullable();
            $table->string('key');
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    private function seedCoreContext(): void
    {
        $departmentId = (string) Str::uuid();
        $moduleId = (string) Str::uuid();

        DB::table('departments')->insert([
            'id' => $departmentId,
            'code' => 'ITO',
            'name' => 'Information Technology Office',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('modules')->insert([
            'id' => $moduleId,
            'code' => 'CORE',
            'name' => 'Core Platform',
            'type' => 'platform',
            'default_department_id' => $departmentId,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(array $overrides = []): User
    {
        return User::query()->create(array_merge([
            'id' => (string) Str::uuid(),
            'username' => 'staff-' . substr((string) Str::uuid(), 0, 8),
            'email' => 'staff-' . substr((string) Str::uuid(), 0, 8) . '@example.com',
            'password' => Hash::make('old-password'),
            'user_type' => 'Viewer',
            'is_active' => true,
            'must_change_password' => false,
        ], $overrides));
    }
}
