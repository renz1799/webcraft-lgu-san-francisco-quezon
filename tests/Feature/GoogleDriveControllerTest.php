<?php

namespace Tests\Feature;

use App\Core\Http\Controllers\GoogleDriveController;
use App\Core\Services\Access\ModuleDepartmentResolver;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveConnectionServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class GoogleDriveControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::dropAllTables();

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
            $table->string('password')->default('secret');
            $table->string('user_type')->default('Admin');
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
            $table->text('address')->nullable();
            $table->text('contact_details')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('google_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->uuid('department_id');
            $table->uuid('connected_by_user_id')->nullable();
            $table->string('provider', 100)->default('google_drive');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        DB::table('departments')->insert([
            [
                'id' => 'department-core',
                'code' => 'ITO',
                'name' => 'Information Technology Office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'department-gso',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('modules')->insert([
            [
                'id' => 'module-core',
                'code' => 'CORE',
                'name' => 'Core Platform',
                'type' => 'platform',
                'default_department_id' => 'department-core',
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

        DB::table('users')->insert([
            [
                'id' => 'user-admin',
                'primary_department_id' => 'department-core',
                'username' => 'drive-admin',
                'email' => 'drive-admin@example.com',
                'password' => 'secret',
                'user_type' => 'Admin',
                'is_active' => true,
                'must_change_password' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('user_profiles')->insert([
            [
                'id' => 'profile-admin',
                'user_id' => 'user-admin',
                'first_name' => 'Maria',
                'middle_name' => 'Santos',
                'last_name' => 'Dela Cruz',
                'name_extension' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('google_tokens')->insert([
            [
                'id' => 'token-gso',
                'module_id' => 'module-gso',
                'department_id' => 'department-gso',
                'connected_by_user_id' => 'user-admin',
                'provider' => 'google_drive',
                'access_token' => 'encrypted-access',
                'refresh_token' => 'encrypted-refresh',
                'created_at' => now()->subDay(),
                'updated_at' => now(),
            ],
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_drive_index_contexts_use_profile_full_name_for_connected_by_display(): void
    {
        $controller = new GoogleDriveController(
            Mockery::mock(GoogleDriveConnectionServiceInterface::class),
            Mockery::mock(GoogleDriveFileServiceInterface::class),
            new ModuleDepartmentResolver(new CurrentContext()),
        );

        $view = $controller->index();
        $contexts = collect($view->getData()['contexts']);

        $gsoContext = $contexts
            ->firstWhere('module_code', 'GSO');

        $this->assertNotNull($gsoContext);
        $this->assertSame('Maria Santos Dela Cruz', $gsoContext['connected_by_name']);
        $this->assertTrue($gsoContext['connected']);
    }
}
