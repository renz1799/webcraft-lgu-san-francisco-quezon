<?php

namespace Tests\Feature;

use App\Builders\Tasks\TaskAdminStatsBuilder;
use App\Builders\Tasks\TaskDatatableRowBuilder;
use App\Builders\Tasks\TaskReassignmentNoteBuilder;
use App\Builders\User\UserTaskReassignOptionBuilder;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Models\UserProfile;
use App\Policies\TaskPolicy;
use App\Services\Contracts\Tasks\TaskNotificationServiceInterface;
use App\Services\Tasks\TaskReadService;
use App\Services\Tasks\TaskService;
use App\Services\Tasks\TaskShowActionProvider;
use App\Support\CurrentContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class TaskModuleScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSchema();
        $this->setUpTaskRoutes();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_create_and_assign_stamps_current_module_and_department_context(): void
    {
        $actor = $this->createUserWithModule('user-actor', 'module-1', 'department-1', 'Actor User');
        $assignee = $this->createUserWithModule('user-assignee', 'module-1', 'department-1', 'Assignee User');

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->andReturn('module-1');
        $context->shouldReceive('defaultDepartmentId')->andReturn('department-1');
        app()->instance(CurrentContext::class, $context);

        $notifications = Mockery::mock(TaskNotificationServiceInterface::class);
        $notifications->shouldReceive('notifyAssigned')
            ->once()
            ->withArgs(function (
                Task $task,
                string $actorUserId,
                string $assigneeUserId
            ) use ($actor, $assignee): bool {
                $this->assertNotSame('', (string) $task->id);
                $this->assertSame('module-1', $task->module_id);
                $this->assertSame('department-1', $task->department_id);
                $this->assertSame((string) $assignee->id, $assigneeUserId);
                $this->assertSame((string) $actor->id, $actorUserId);
                $this->assertSame('Review workflow packet', $task->title);

                return true;
            })
            ->andReturn(new Notification());

        $service = new TaskService(
            app(\App\Repositories\Contracts\TaskRepositoryInterface::class),
            app(\App\Repositories\Contracts\TaskEventRepositoryInterface::class),
            app(\App\Repositories\Contracts\UserRepositoryInterface::class),
            $notifications,
            $context,
            new TaskReassignmentNoteBuilder(new UserTaskReassignOptionBuilder()),
        );

        $task = $service->createAndAssign(
            actorUserId: (string) $actor->id,
            assigneeUserId: (string) $assignee->id,
            title: 'Review workflow packet',
            description: 'Check the queue item before release.',
            type: 'workflow_review',
            subjectType: 'workflow',
            subjectId: 'subject-1',
            data: ['eligible_roles' => ['Staff']],
        );

        $this->assertSame('module-1', $task->module_id);
        $this->assertSame('department-1', $task->department_id);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'module_id' => 'module-1',
            'department_id' => 'department-1',
            'assigned_to_user_id' => $assignee->id,
        ]);
        $this->assertDatabaseCount('task_events', 2);
    }

    public function test_task_reads_are_scoped_to_the_current_module(): void
    {
        $actor = $this->createUserWithModule('user-actor', 'module-1', 'department-1', 'Actor User');

        $currentTask = Task::query()->create([
            'module_id' => 'module-1',
            'department_id' => 'department-1',
            'title' => 'Current Module Task',
            'status' => Task::STATUS_PENDING,
            'created_by_user_id' => $actor->id,
            'assigned_to_user_id' => $actor->id,
            'data' => [],
        ]);

        $otherTask = Task::query()->create([
            'module_id' => 'module-2',
            'department_id' => 'department-2',
            'title' => 'Other Module Task',
            'status' => Task::STATUS_PENDING,
            'created_by_user_id' => $actor->id,
            'assigned_to_user_id' => $actor->id,
            'data' => [],
        ]);

        $context = Mockery::mock(CurrentContext::class);
        $context->shouldReceive('moduleId')->andReturn('module-1');
        $context->shouldReceive('defaultDepartmentId')->andReturn('department-1');
        app()->instance(CurrentContext::class, $context);

        $readService = new TaskReadService(
            app(\App\Repositories\Contracts\TaskRepositoryInterface::class),
            app(\App\Repositories\Contracts\TaskEventRepositoryInterface::class),
            app(\App\Repositories\Contracts\UserRepositoryInterface::class),
            new TaskPolicy(),
            $context,
            new TaskDatatableRowBuilder(),
            new TaskAdminStatsBuilder(),
            new TaskShowActionProvider(),
            new UserTaskReassignOptionBuilder(),
        );

        $actorWithPermissions = $this->mockActor($actor, roles: ['Staff'], permissions: ['view All Tasks']);

        $payload = $readService->datatable($actorWithPermissions, [
            'scope' => 'all',
            'page' => 1,
            'size' => 15,
        ]);

        $this->assertSame(1, $payload['total']);
        $this->assertCount(1, $payload['data']);
        $this->assertSame((string) $currentTask->id, $payload['data'][0]['id']);

        $this->expectException(ModelNotFoundException::class);
        $readService->findOrFail((string) $otherTask->id);
    }

    private function setUpSchema(): void
    {
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

        Schema::create('task_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('task_id');
            $table->uuid('actor_user_id');
            $table->string('actor_name_snapshot');
            $table->string('actor_username_snapshot')->nullable();
            $table->string('event_type');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function setUpTaskRoutes(): void
    {
        if (! Route::has('tasks.show')) {
            Route::get('/tasks/{id}', fn () => 'show')->name('tasks.show');
        }

        if (! Route::has('tasks.claim')) {
            Route::post('/tasks/{id}/claim', fn () => 'claim')->name('tasks.claim');
        }

        if (! Route::has('tasks.destroy')) {
            Route::delete('/tasks/{id}', fn () => 'destroy')->name('tasks.destroy');
        }

        if (! Route::has('tasks.restore')) {
            Route::patch('/tasks/{id}/restore', fn () => 'restore')->name('tasks.restore');
        }
    }

    private function createUserWithModule(string $id, string $moduleId, string $departmentId, string $fullName): User
    {
        $user = User::query()->create([
            'id' => $id,
            'primary_department_id' => $departmentId,
            'username' => $id,
            'email' => "{$id}@example.com",
            'password' => 'secret',
            'is_active' => true,
        ]);

        [$firstName, $lastName] = explode(' ', $fullName, 2);

        UserProfile::query()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);

        \DB::table('user_modules')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'module_id' => $moduleId,
            'department_id' => $departmentId,
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
