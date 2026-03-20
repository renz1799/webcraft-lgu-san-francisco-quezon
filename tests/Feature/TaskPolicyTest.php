<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use App\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('primary_department_id')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->boolean('must_change_password')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('user_type')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
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

        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedTinyInteger('priority')->default(0);
            $table->uuid('created_by_user_id');
            $table->uuid('assigned_to_user_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->uuid('subject_id')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_view_denies_task_from_another_module(): void
    {
        $user = $this->createUserWithModule('user-1', 'module-1');

        $task = new Task([
            'id' => 'task-1',
            'module_id' => 'module-2',
            'created_by_user_id' => 'user-2',
            'assigned_to_user_id' => null,
            'status' => Task::STATUS_PENDING,
            'data' => [],
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->andReturn('module-1');
        app()->instance(CurrentContext::class, $context);

        $actor = $this->mockActor($user, roles: ['Staff']);

        $this->assertFalse((new TaskPolicy())->view($actor, $task));
    }

    public function test_assigned_user_can_comment_and_update_status_in_current_module(): void
    {
        $user = $this->createUserWithModule('user-1', 'module-1');
        $other = $this->createUserWithModule('user-2', 'module-1');

        $task = new Task([
            'id' => 'task-1',
            'module_id' => 'module-1',
            'created_by_user_id' => $other->id,
            'assigned_to_user_id' => $user->id,
            'status' => Task::STATUS_PENDING,
            'data' => [],
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->andReturn('module-1');
        app()->instance(CurrentContext::class, $context);

        $policy = new TaskPolicy();
        $assignedActor = $this->mockActor($user, roles: ['Staff']);
        $unrelatedActor = $this->mockActor($other, roles: ['Staff']);

        $this->assertTrue($policy->comment($assignedActor, $task));
        $this->assertTrue($policy->updateStatus($assignedActor, $task));
        $this->assertFalse($policy->updateStatus($unrelatedActor, $task));
    }

    public function test_reassign_allows_permission_based_actor_in_current_module(): void
    {
        $user = $this->createUserWithModule('user-1', 'module-1');

        $task = new Task([
            'id' => 'task-1',
            'module_id' => 'module-1',
            'created_by_user_id' => 'user-2',
            'assigned_to_user_id' => null,
            'status' => Task::STATUS_PENDING,
            'data' => [],
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->andReturn('module-1');
        app()->instance(CurrentContext::class, $context);

        $actor = $this->mockActor($user, roles: ['Staff'], permissions: ['modify Reassign Tasks']);

        $this->assertTrue((new TaskPolicy())->reassign($actor, $task));
    }

    private function createUserWithModule(string $id, string $moduleId): User
    {
        $user = User::query()->create([
            'id' => $id,
            'username' => $id,
            'email' => "{$id}@example.com",
            'password' => 'secret',
            'is_active' => true,
        ]);

        \DB::table('user_modules')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'module_id' => $moduleId,
            'department_id' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $user;
    }

    private function mockActor(User $user, array $roles = [], array $permissions = []): User
    {
        $actor = Mockery::mock($user)->makePartial();
        $roleCollection = new Collection($roles);

        $actor->shouldReceive('hasAnyRole')
            ->andReturnUsing(static fn (array $check): bool => count(array_intersect($check, $roles)) > 0);
        $actor->shouldReceive('can')
            ->andReturnUsing(static fn (string $permission): bool => in_array($permission, $permissions, true));
        $actor->shouldReceive('getRoleNames')
            ->andReturn($roleCollection);

        return $actor;
    }
}
