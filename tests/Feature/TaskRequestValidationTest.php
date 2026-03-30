<?php

namespace Tests\Feature;

use App\Core\Http\Requests\Tasks\ReassignTaskRequest;
use App\Core\Http\Requests\Tasks\StoreTaskRequest;
use App\Core\Http\Requests\Tasks\TaskTableDataRequest;
use App\Core\Models\Tasks\Task;
use App\Core\Models\User;
use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Mockery;
use Tests\TestCase;

class TaskRequestValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

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

    public function test_store_task_request_requires_assignee_from_current_module(): void
    {
        $inModule = $this->createUserWithModule('user-1', 'module-1');
        $outOfModule = $this->createUserWithModule('user-2', 'module-2');

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->andReturn('module-1');
        app()->instance(CurrentContext::class, $context);

        $request = StoreTaskRequest::create('/tasks', 'POST', [
            'assignee_user_id' => $inModule->id,
            'title' => 'Valid Task',
        ]);
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->mockActor($inModule, ['Administrator']));
        $this->mockAuthorizerForPermission('tasks.create', true);

        $this->assertTrue($request->authorize());
        $this->assertTrue(Validator::make($request->all(), $request->rules())->passes());

        $invalidRequest = StoreTaskRequest::create('/tasks', 'POST', [
            'assignee_user_id' => $outOfModule->id,
            'title' => 'Invalid Task',
        ]);
        $invalidRequest->setContainer($this->app);
        $invalidRequest->setUserResolver(fn () => $this->mockActor($inModule, ['Administrator']));
        $this->mockAuthorizerForPermission('tasks.create', true);

        $validator = Validator::make($invalidRequest->all(), $invalidRequest->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('assignee_user_id', $validator->errors()->toArray());
    }

    public function test_reassign_request_allows_permission_and_uses_current_module_validation(): void
    {
        $inModule = $this->createUserWithModule('user-1', 'module-1');
        $outOfModule = $this->createUserWithModule('user-2', 'module-2');
        $this->createTask('task-1', 'module-1', $inModule->id);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->andReturn('module-1');
        app()->instance(CurrentContext::class, $context);

        $request = ReassignTaskRequest::create('/tasks/reassign', 'POST', [
            'assignee_user_id' => $outOfModule->id,
        ]);
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->mockActor($inModule, ['Staff'], ['tasks.reassign']));
        $request->setRouteResolver(function () {
            $route = Mockery::mock();
            $route->shouldReceive('parameter')
                ->with('id', null)
                ->andReturn('task-1');
            $route->shouldReceive('parameter')
                ->with('task', null)
                ->andReturn(null);

            return $route;
        });
        $this->mockAuthorizerForPermission('tasks.reassign', true);

        $this->assertTrue($request->authorize());
        $this->assertFalse(Validator::make($request->all(), $request->rules())->passes());
    }

    public function test_task_table_request_normalizes_defaults_and_rejects_invalid_date_ranges(): void
    {
        $user = $this->createUserWithModule('user-1', 'module-1');

        $request = TestableTaskTableDataRequest::create('/tasks/data', 'GET', [
            'search' => '  hello  ',
            'date_from' => '2026-03-21',
            'date_to' => '2026-03-20',
        ]);
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->mockActor($user, ['Staff']));
        $request->normalizeForTest();
        $this->mockAuthorizerForPermission('tasks.view', true);

        $this->assertTrue($request->authorize());

        $invalid = Validator::make($request->all(), $request->rules());
        $this->assertFalse($invalid->passes());
        $this->assertArrayHasKey('date_to', $invalid->errors()->toArray());

        $validRequest = TestableTaskTableDataRequest::create('/tasks/data', 'GET', [
            'search' => '  hello  ',
        ]);
        $validRequest->setContainer($this->app);
        $validRequest->setUserResolver(fn () => $this->mockActor($user, ['Staff']));
        $validRequest->normalizeForTest();
        $this->mockAuthorizerForPermission('tasks.view', true);

        $validator = Validator::make($validRequest->all(), $validRequest->rules());
        $validRequest->setValidator($validator);

        $this->assertTrue($validator->passes());

        $validated = $validRequest->validated();

        $this->assertSame('hello', $validated['search']);
        $this->assertSame(1, $validated['page']);
        $this->assertSame(15, $validated['size']);
        $this->assertSame('active', $validated['archived']);
        $this->assertSame('mine', $validated['scope']);
    }

    public function test_task_table_request_requires_view_all_permission_for_all_scope(): void
    {
        $user = $this->createUserWithModule('user-1', 'module-1');

        $request = TestableTaskTableDataRequest::create('/tasks/data', 'GET', [
            'scope' => 'all',
        ]);
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->mockActor($user, ['Staff']));
        $request->normalizeForTest();
        $this->mockAuthorizerForPermission('tasks.view_all', true);

        $this->assertTrue($request->authorize());
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

    private function createTask(string $id, string $moduleId, string $createdByUserId): void
    {
        Task::query()->create([
            'id' => $id,
            'module_id' => $moduleId,
            'department_id' => null,
            'title' => 'Task for validation',
            'status' => Task::STATUS_PENDING,
            'created_by_user_id' => $createdByUserId,
            'assigned_to_user_id' => null,
            'data' => [],
        ]);
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

    private function mockAuthorizerForPermission(string $permission, bool $result): void
    {
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('allowsPermission')
            ->withArgs(function ($user, string $requestedPermission) use ($permission): bool {
                return $requestedPermission === $permission;
            })
            ->andReturn($result);

        app()->instance(AdminContextAuthorizer::class, $authorizer);
    }
}

class TestableTaskTableDataRequest extends TaskTableDataRequest
{
    public function normalizeForTest(): void
    {
        $this->prepareForValidation();
    }
}
