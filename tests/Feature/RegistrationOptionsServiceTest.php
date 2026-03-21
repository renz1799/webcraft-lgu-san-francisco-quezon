<?php

namespace Tests\Feature;

use App\Core\Builders\Auth\RegistrationRoleOptionsBuilder;
use App\Core\Models\Role;
use App\Core\Services\Auth\RegistrationOptionsService;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class RegistrationOptionsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_roles_returns_current_module_non_admin_roles_only(): void
    {
        Role::query()->create(['id' => 'role-1', 'module_id' => 'module-1', 'name' => 'Staff', 'guard_name' => 'web']);
        Role::query()->create(['id' => 'role-2', 'module_id' => 'module-1', 'name' => 'Administrator', 'guard_name' => 'web']);
        Role::query()->create(['id' => 'role-3', 'module_id' => 'module-2', 'name' => 'Inspector', 'guard_name' => 'web']);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->once()->andReturn('module-1');

        $service = new RegistrationOptionsService(new RegistrationRoleOptionsBuilder(), $context);

        $roles = $service->roles();

        $this->assertSame(['Staff'], $roles->pluck('name')->all());
    }
}
