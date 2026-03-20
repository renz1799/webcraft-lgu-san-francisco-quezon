<?php

namespace Tests\Feature;

use App\Builders\Tasks\TaskNotificationPayloadBuilder;
use App\Models\Notification;
use App\Models\Task;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\NotificationServiceInterface;
use App\Services\Tasks\TaskNotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class TaskNotificationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Route::has('tasks.show')) {
            Route::get('/tasks/{task}', fn () => 'task')->name('tasks.show');
        }
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_notify_assigned_uses_task_payload_builder_and_generic_notification_transport(): void
    {
        $notifications = Mockery::mock(NotificationServiceInterface::class);
        $taskEvents = Mockery::mock(TaskEventRepositoryInterface::class);
        $users = Mockery::mock(UserRepositoryInterface::class);

        $task = new Task([
            'title' => 'Review AIR draft',
            'module_id' => 'module-1',
            'department_id' => 'department-1',
        ]);
        $task->id = 'task-1';

        $notifications->shouldReceive('notifyUser')
            ->once()
            ->withArgs(function (
                string $notifiableUserId,
                string $actorUserId,
                string $type,
                string $title,
                string $message,
                string $entityType,
                string $entityId,
                array $data,
                ?string $moduleId,
                ?string $departmentId
            ): bool {
                $this->assertSame('assignee-1', $notifiableUserId);
                $this->assertSame('actor-1', $actorUserId);
                $this->assertSame('task_assigned', $type);
                $this->assertSame('New Task Assigned', $title);
                $this->assertSame('You were assigned: Review AIR draft', $message);
                $this->assertSame('tasks', $entityType);
                $this->assertSame('task-1', $entityId);
                $this->assertSame('task-1', $data['task_id']);
                $this->assertSame(route('tasks.show', 'task-1'), $data['url']);
                $this->assertSame('module-1', $moduleId);
                $this->assertSame('department-1', $departmentId);

                return true;
            })
            ->andReturn(new Notification());

        $service = new TaskNotificationService(
            $notifications,
            $taskEvents,
            $users,
            new TaskNotificationPayloadBuilder(),
        );

        $service->notifyAssigned($task, 'actor-1', 'assignee-1');
    }

    public function test_notify_reassigned_notifies_participants_and_new_assignee(): void
    {
        $notifications = Mockery::mock(NotificationServiceInterface::class);
        $taskEvents = Mockery::mock(TaskEventRepositoryInterface::class);
        $users = Mockery::mock(UserRepositoryInterface::class);

        $task = new Task([
            'title' => 'Review AIR draft',
            'module_id' => 'module-1',
            'department_id' => 'department-1',
            'created_by_user_id' => 'creator-1',
            'assigned_to_user_id' => 'assignee-2',
        ]);
        $task->id = 'task-1';

        $taskEvents->shouldReceive('getForTask')
            ->once()
            ->with('task-1')
            ->andReturn(new Collection([
                (object) ['actor_user_id' => 'participant-1'],
                (object) ['actor_user_id' => 'assignee-2'],
            ]));

        $users->shouldReceive('getUserIdsByRoles')
            ->once()
            ->with(['Administrator', 'admin'])
            ->andReturn(['admin-1']);

        $notifications->shouldReceive('notifyUsers')
            ->once()
            ->withArgs(function (
                array $recipientUserIds,
                string $actorUserId,
                string $type,
                string $title,
                string $message,
                string $entityType,
                string $entityId,
                array $data,
                ?string $moduleId,
                ?string $departmentId
            ): bool {
                sort($recipientUserIds);

                $this->assertSame(['admin-1', 'assignee-2', 'creator-1', 'participant-1'], $recipientUserIds);
                $this->assertSame('actor-1', $actorUserId);
                $this->assertSame('task_reassigned', $type);
                $this->assertSame('Task Reassigned', $title);
                $this->assertSame('Task "Review AIR draft" was reassigned.', $message);
                $this->assertSame('tasks', $entityType);
                $this->assertSame('task-1', $entityId);
                $this->assertSame(route('tasks.show', 'task-1'), $data['url']);
                $this->assertSame('module-1', $moduleId);
                $this->assertSame('department-1', $departmentId);

                return true;
            });

        $notifications->shouldReceive('notifyUser')
            ->once()
            ->withArgs(function (...$args): bool {
                [$notifiableUserId, $actorUserId, $type, $title] = $args;

                $this->assertSame('assignee-2', $notifiableUserId);
                $this->assertSame('actor-1', $actorUserId);
                $this->assertSame('task_assigned', $type);
                $this->assertSame('New Task Assigned', $title);

                return true;
            })
            ->andReturn(new Notification());

        $service = new TaskNotificationService(
            $notifications,
            $taskEvents,
            $users,
            new TaskNotificationPayloadBuilder(),
        );

        $service->notifyReassigned($task, 'actor-1', 'assignee-2');
    }
}
