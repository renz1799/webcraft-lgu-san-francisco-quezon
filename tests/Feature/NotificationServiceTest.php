<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
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

    public function test_notify_users_by_roles_fans_out_to_role_members_and_excludes_actor_by_default(): void
    {
        $notifications = Mockery::mock(NotificationRepositoryInterface::class);
        $taskEvents = Mockery::mock(TaskEventRepositoryInterface::class);
        $users = Mockery::mock(UserRepositoryInterface::class);

        $users->shouldReceive('getUserIdsByRoles')
            ->once()
            ->with(['Administrator', 'Staff'])
            ->andReturn(['actor-1', 'staff-2', 'admin-3', 'staff-2']);

        $notifications->shouldReceive('insertMany')
            ->once()
            ->with(Mockery::on(function (array $rows): bool {
                $this->assertCount(2, $rows);
                $this->assertSame(['admin-3', 'staff-2'], collect($rows)->pluck('notifiable_user_id')->sort()->values()->all());

                foreach ($rows as $row) {
                    $this->assertSame('actor-1', $row['actor_user_id']);
                    $this->assertSame('workflow_submitted', $row['type']);
                    $this->assertSame('Workflow Submitted', $row['title']);
                    $this->assertSame('AIR submitted for review.', $row['message']);
                    $this->assertSame('air', $row['entity_type']);
                    $this->assertSame('air-1', $row['entity_id']);

                    $data = json_decode($row['data'], true, 512, JSON_THROW_ON_ERROR);

                    $this->assertSame('air-1', $data['air_id']);
                    $this->assertSame('/air/air-1', $data['url']);
                }

                return true;
            }));

        $service = new NotificationService($notifications, $taskEvents, $users);

        $service->notifyUsersByRoles(
            roleNames: ['Administrator', 'Staff'],
            actorUserId: 'actor-1',
            type: 'workflow_submitted',
            title: 'Workflow Submitted',
            message: 'AIR submitted for review.',
            entityType: 'air',
            entityId: 'air-1',
            data: [
                'air_id' => 'air-1',
                'url' => '/air/air-1',
            ],
        );
    }

    public function test_notify_task_assigned_uses_default_task_url_when_url_is_not_provided(): void
    {
        $notifications = Mockery::mock(NotificationRepositoryInterface::class);
        $taskEvents = Mockery::mock(TaskEventRepositoryInterface::class);
        $users = Mockery::mock(UserRepositoryInterface::class);

        $notifications->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                $this->assertSame('assignee-1', $payload['notifiable_user_id']);
                $this->assertSame('actor-1', $payload['actor_user_id']);
                $this->assertSame('task_assigned', $payload['type']);
                $this->assertSame('tasks', $payload['entity_type']);
                $this->assertSame('task-1', $payload['entity_id']);
                $this->assertSame(route('tasks.show', 'task-1'), $payload['data']['url']);

                return true;
            }))
            ->andReturnUsing(fn (array $payload) => new Notification($payload));

        $service = new NotificationService($notifications, $taskEvents, $users);

        $notification = $service->notifyTaskAssigned(
            assigneeUserId: 'assignee-1',
            actorUserId: 'actor-1',
            taskId: 'task-1',
            taskTitle: 'Review AIR draft',
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertSame(route('tasks.show', 'task-1'), $notification->data['url']);
    }

    public function test_notify_inspection_submitted_wraps_the_generic_role_based_notification_flow(): void
    {
        $notifications = Mockery::mock(NotificationRepositoryInterface::class);
        $taskEvents = Mockery::mock(TaskEventRepositoryInterface::class);
        $users = Mockery::mock(UserRepositoryInterface::class);

        $users->shouldReceive('getUserIdsByRoles')
            ->once()
            ->with(['Administrator', 'Staff'])
            ->andReturn(['actor-1', 'reviewer-2']);

        $notifications->shouldReceive('insertMany')
            ->once()
            ->with(Mockery::on(function (array $rows): bool {
                $this->assertCount(1, $rows);

                $row = $rows[0];

                $this->assertSame('reviewer-2', $row['notifiable_user_id']);
                $this->assertSame('inspection_submitted', $row['type']);
                $this->assertSame('Inspection Submitted', $row['title']);
                $this->assertSame('inspections', $row['entity_type']);
                $this->assertSame('inspection-1', $row['entity_id']);

                $data = json_decode($row['data'], true, 512, JSON_THROW_ON_ERROR);

                $this->assertSame('inspection-1', $data['inspection_id']);
                $this->assertSame('PO-001', $data['po_number']);
                $this->assertSame('DV-001', $data['dv_number']);
                $this->assertSame('submitted', $data['status']);
                $this->assertSame('task-9', $data['task_id']);
                $this->assertSame('/inspections/inspection-1', $data['url']);

                return true;
            }));

        $inspection = (object) [
            'id' => 'inspection-1',
            'po_number' => 'PO-001',
            'dv_number' => 'DV-001',
            'status' => 'submitted',
            'url' => '/inspections/inspection-1',
        ];

        $service = new NotificationService($notifications, $taskEvents, $users);

        $service->notifyInspectionSubmitted(
            inspection: $inspection,
            actorUserId: 'actor-1',
            taskId: 'task-9',
        );
    }
}