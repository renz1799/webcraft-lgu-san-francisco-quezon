<?php

namespace Tests\Feature;

use App\Core\Models\User;
use Database\Seeders\LegacyPermissionAssignmentMigrationSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LegacyPermissionAssignmentMigrationSeederTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createPermissionSchema();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        parent::tearDown();
    }

    public function test_it_migrates_legacy_role_and_direct_assignments_to_normalized_permissions(): void
    {
        DB::table('permissions')->insert([
            [
                'id' => 'permission-legacy',
                'module_id' => 'module-gso',
                'name' => 'modify Asset Types',
                'page' => 'GSO / Reference Data',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'permission-create',
                'module_id' => 'module-gso',
                'name' => 'asset_types.create',
                'page' => 'GSO / Reference Data',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'permission-update',
                'module_id' => 'module-gso',
                'name' => 'asset_types.update',
                'page' => 'GSO / Reference Data',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'permission-archive',
                'module_id' => 'module-gso',
                'name' => 'asset_types.archive',
                'page' => 'GSO / Reference Data',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'permission-restore',
                'module_id' => 'module-gso',
                'name' => 'asset_types.restore',
                'page' => 'GSO / Reference Data',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('role_has_permissions')->insert([
            'permission_id' => 'permission-legacy',
            'role_id' => 'role-1',
        ]);

        DB::table('model_has_permissions')->insert([
            'permission_id' => 'permission-legacy',
            'module_id' => 'module-gso',
            'model_type' => User::class,
            'model_id' => 'user-1',
        ]);

        $this->seed(LegacyPermissionAssignmentMigrationSeeder::class);

        foreach (['permission-create', 'permission-update', 'permission-archive', 'permission-restore'] as $permissionId) {
            $this->assertDatabaseHas('role_has_permissions', [
                'permission_id' => $permissionId,
                'role_id' => 'role-1',
            ]);

            $this->assertDatabaseHas('model_has_permissions', [
                'permission_id' => $permissionId,
                'module_id' => 'module-gso',
                'model_type' => User::class,
                'model_id' => 'user-1',
            ]);
        }

        $this->assertDatabaseMissing('role_has_permissions', [
            'permission_id' => 'permission-legacy',
            'role_id' => 'role-1',
        ]);

        $this->assertDatabaseMissing('model_has_permissions', [
            'permission_id' => 'permission-legacy',
            'module_id' => 'module-gso',
            'model_type' => User::class,
            'model_id' => 'user-1',
        ]);

        $this->assertDatabaseHas('permissions', [
            'id' => 'permission-legacy',
            'name' => 'modify Asset Types',
        ]);

        $this->assertNotNull(
            DB::table('permissions')
                ->where('id', 'permission-legacy')
                ->value('deleted_at')
        );
    }

    public function test_it_keeps_legacy_assignment_when_normalized_targets_are_incomplete(): void
    {
        DB::table('permissions')->insert([
            [
                'id' => 'permission-legacy',
                'module_id' => 'module-gso',
                'name' => 'modify Asset Types',
                'page' => 'GSO / Reference Data',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'permission-create',
                'module_id' => 'module-gso',
                'name' => 'asset_types.create',
                'page' => 'GSO / Reference Data',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('role_has_permissions')->insert([
            'permission_id' => 'permission-legacy',
            'role_id' => 'role-1',
        ]);

        $this->seed(LegacyPermissionAssignmentMigrationSeeder::class);

        $this->assertDatabaseHas('role_has_permissions', [
            'permission_id' => 'permission-legacy',
            'role_id' => 'role-1',
        ]);

        $this->assertNull(
            DB::table('permissions')
                ->where('id', 'permission-legacy')
                ->value('deleted_at')
        );
    }

    private function createPermissionSchema(): void
    {
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
            $table->unique(['permission_id', 'role_id']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->uuid('permission_id');
            $table->uuid('module_id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->unique(['permission_id', 'module_id', 'model_type', 'model_id'], 'model_has_permissions_unique');
        });
    }
}
