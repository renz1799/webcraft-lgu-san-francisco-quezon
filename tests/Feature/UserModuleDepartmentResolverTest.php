<?php

namespace Tests\Feature;

use App\Core\Services\Access\UserModuleDepartmentResolver;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class UserModuleDepartmentResolverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

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

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_prefers_active_module_department_for_the_user(): void
    {
        DB::table('user_modules')->insert([
            [
                'id' => 'user-module-1',
                'user_id' => 'user-1',
                'module_id' => 'module-1',
                'department_id' => null,
                'is_active' => true,
                'granted_at' => '2026-03-21 08:00:00',
                'revoked_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'user-module-2',
                'user_id' => 'user-1',
                'module_id' => 'module-1',
                'department_id' => 'department-1',
                'is_active' => true,
                'granted_at' => '2026-03-21 09:00:00',
                'revoked_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->never();

        $resolver = new UserModuleDepartmentResolver($context);

        $resolved = $resolver->resolveForUser('user-1', 'module-1');

        $this->assertSame('department-1', $resolved);
    }

    public function test_it_uses_current_context_module_when_module_is_not_provided(): void
    {
        DB::table('user_modules')->insert([
            'id' => 'user-module-3',
            'user_id' => 'user-2',
            'module_id' => 'module-2',
            'department_id' => 'department-2',
            'is_active' => true,
            'granted_at' => '2026-03-21 10:00:00',
            'revoked_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->once()->andReturn('module-2');

        $resolver = new UserModuleDepartmentResolver($context);

        $resolved = $resolver->resolveForUser('user-2');

        $this->assertSame('department-2', $resolved);
    }

    public function test_it_returns_null_when_user_or_module_context_is_missing(): void
    {
        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->once()->andReturn(null);

        $resolver = new UserModuleDepartmentResolver($context);

        $this->assertNull($resolver->resolveForUser(null, 'module-1'));
        $this->assertNull($resolver->resolveForUser('user-1'));
    }
}
