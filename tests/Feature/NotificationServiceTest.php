<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Notifications\NotificationService;
use App\Support\CurrentContext;
use Mockery;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_notify_users_by_roles_fans_out_to_role_members_and_excludes_actor_by_default(): void
    {
        $notifications = Mockery::mock(NotificationRepositoryInterface::class);
        $users = Mockery::mock(UserRepositoryInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $context->shouldReceive('moduleId')->andReturn('module-1');
        $context->shouldReceive('defaultDepartmentId')->andReturn('department-1');

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
                    $this->assertSame('module-1', $row['module_id']);
                    $this->assertSame('department-1', $row['department_id']);

                    $data = json_decode($row['data'], true, 512, JSON_THROW_ON_ERROR);

                    $this->assertSame('air-1', $data['air_id']);
                    $this->assertSame('/air/air-1', $data['url']);
                }

                return true;
            }));

        $service = new NotificationService($notifications, $users, $context);

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

    public function test_notify_user_uses_current_context_defaults_when_module_and_department_are_not_provided(): void
    {
        $notifications = Mockery::mock(NotificationRepositoryInterface::class);
        $users = Mockery::mock(UserRepositoryInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $context->shouldReceive('moduleId')->andReturn('module-1');
        $context->shouldReceive('defaultDepartmentId')->andReturn('department-1');

        $notifications->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $payload): bool {
                $this->assertSame('recipient-1', $payload['notifiable_user_id']);
                $this->assertSame('actor-1', $payload['actor_user_id']);
                $this->assertSame('task_assigned', $payload['type']);
                $this->assertSame('tasks', $payload['entity_type']);
                $this->assertSame('task-1', $payload['entity_id']);
                $this->assertSame('module-1', $payload['module_id']);
                $this->assertSame('department-1', $payload['department_id']);
                $this->assertSame('/tasks/task-1', $payload['data']['url']);

                return true;
            }))
            ->andReturnUsing(fn (array $payload) => new Notification($payload));

        $service = new NotificationService($notifications, $users, $context);

        $notification = $service->notifyUser(
            notifiableUserId: 'recipient-1',
            actorUserId: 'actor-1',
            type: 'task_assigned',
            title: 'New Task Assigned',
            message: 'You were assigned: Review AIR draft',
            entityType: 'tasks',
            entityId: 'task-1',
            data: [
                'task_id' => 'task-1',
                'url' => '/tasks/task-1',
            ],
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertSame('/tasks/task-1', $notification->data['url']);
    }

    public function test_notify_users_deduplicates_and_filters_empty_recipient_ids(): void
    {
        $notifications = Mockery::mock(NotificationRepositoryInterface::class);
        $users = Mockery::mock(UserRepositoryInterface::class);
        $context = Mockery::mock(CurrentContext::class);

        $context->shouldReceive('moduleId')->andReturn('module-1');
        $context->shouldReceive('defaultDepartmentId')->andReturn('department-1');

        $notifications->shouldReceive('insertMany')
            ->once()
            ->with(Mockery::on(function (array $rows): bool {
                $this->assertCount(2, $rows);
                $this->assertSame(['reviewer-2', 'reviewer-3'], collect($rows)->pluck('notifiable_user_id')->sort()->values()->all());

                return true;
            }));

        $service = new NotificationService($notifications, $users, $context);

        $service->notifyUsers(
            recipientUserIds: ['reviewer-2', '', 'reviewer-2', 'reviewer-3', null],
            actorUserId: 'actor-1',
            type: 'inspection_submitted',
            title: 'Inspection Submitted',
            message: 'Inspection submitted for review.',
            entityType: 'inspections',
            entityId: 'inspection-1',
            data: ['inspection_id' => 'inspection-1'],
        );
    }
}
