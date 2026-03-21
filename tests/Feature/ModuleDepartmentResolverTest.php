<?php

namespace Tests\Feature;

use App\Core\Models\Department;
use App\Core\Models\Module;
use App\Core\Services\Access\ModuleDepartmentResolver;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ModuleDepartmentResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->uuid('default_department_id')->nullable();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->softDeletes();
        });

        DB::table('modules')->insert([
            ['id' => 'module-core', 'code' => 'CORE', 'name' => 'Core', 'default_department_id' => null],
            ['id' => 'module-dts', 'code' => 'DTS', 'name' => 'DTS', 'default_department_id' => null],
            ['id' => 'module-gso', 'code' => 'GSO', 'name' => 'GSO', 'default_department_id' => null],
            ['id' => 'module-procurement', 'code' => 'PROCUREMENT', 'name' => 'Procurement', 'default_department_id' => 'department-gso'],
        ]);

        DB::table('departments')->insert([
            ['id' => 'department-ito', 'code' => 'ITO', 'name' => 'Information Technology Office'],
            ['id' => 'department-records', 'code' => 'RECORDS', 'name' => 'Records Management Office'],
            ['id' => 'department-gso', 'code' => 'GSO', 'name' => 'General Services Office'],
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_resolves_db_backed_module_department_before_config(): void
    {
        config()->set('modules.department_defaults', [
            'PROCUREMENT' => ['code' => 'BAC'],
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('module')->once()->andReturnNull();
        $context->shouldReceive('defaultDepartmentId')->never();

        $resolver = new ModuleDepartmentResolver($context);

        $resolved = $resolver->resolveForModule('module-procurement');

        $this->assertSame('department-gso', $resolved);
    }

    public function test_it_resolves_module_department_from_config_when_db_default_is_missing(): void
    {
        config()->set('modules.department_defaults', [
            'DTS' => ['code' => 'RECORDS'],
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('module')->once()->andReturnNull();
        $context->shouldReceive('defaultDepartmentId')->never();

        $resolver = new ModuleDepartmentResolver($context);

        $resolved = $resolver->resolveForModule('module-dts');

        $this->assertSame('department-records', $resolved);
    }

    public function test_it_falls_back_to_platform_default_department_when_module_mapping_is_missing(): void
    {
        config()->set('modules.department_defaults', [
            'DTS' => ['code' => 'RECORDS'],
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('module')->once()->andReturnNull();
        $context->shouldReceive('defaultDepartmentId')->once()->andReturn('department-ito');

        $resolver = new ModuleDepartmentResolver($context);

        $resolved = $resolver->resolveForModule('module-gso');

        $this->assertSame('department-ito', $resolved);
    }

    public function test_it_uses_explicit_department_when_provided(): void
    {
        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('module')->never();
        $context->shouldReceive('defaultDepartmentId')->never();

        $resolver = new ModuleDepartmentResolver($context);

        $resolved = $resolver->resolveForModule('module-dts', 'department-gso');

        $this->assertSame('department-gso', $resolved);
    }

    public function test_it_throws_when_explicit_department_does_not_exist(): void
    {
        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('module')->never();
        $context->shouldReceive('defaultDepartmentId')->never();

        $resolver = new ModuleDepartmentResolver($context);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Department [missing-department] was not found.');

        $resolver->resolveForModule('module-dts', 'missing-department');
    }
}
