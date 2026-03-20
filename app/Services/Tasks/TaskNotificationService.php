<?php

namespace App\Services\Tasks;

use App\Builders\Contracts\Tasks\TaskNotificationPayloadBuilderInterface;
use App\Models\Notification;
use App\Models\Task;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\Notifications\NotificationServiceInterface;
use App\Services\Contracts\Tasks\TaskNotificationServiceInterface;

class TaskNotificationService implements TaskNotificationServiceInterface
{
    public function __construct(
        private readonly NotificationServiceInterface $notifications,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly UserRepositoryInterface $users,
        private readonly TaskNotificationPayloadBuilderInterface $taskNotificationPayloadBuilder,
    ) {}

    public function notifyAssigned(Task $task, string $actorUserId, string $assigneeUserId): Notification
    {
        $payload = $this->taskNotificationPayloadBuilder->buildAssigned($task);

        return $this->notifications->notifyUser(
            notifiableUserId: $assigneeUserId,
            actorUserId: $actorUserId,
            type: $payload['type'],
            title: $payload['title'],
            message: $payload['message'],
            entityType: $payload['entity_type'],
            entityId: $payload['entity_id'],
            data: $payload['data'],
            moduleId: $task->module_id ? (string) $task->module_id : null,
            departmentId: $task->department_id ? (string) $task->department_id : null,
        );
    }

    public function notifyStatusChanged(Task $task, string $actorUserId, string $fromStatus, string $toStatus): void
    {
        $payload = $this->taskNotificationPayloadBuilder->buildStatusChanged($task, $fromStatus, $toStatus);

        $this->notifyParticipants($task, $actorUserId, $payload);
    }

    public function notifyReassigned(Task $task, string $actorUserId, string $newAssigneeUserId): void
    {
        $payload = $this->taskNotificationPayloadBuilder->buildReassigned($task);

        $this->notifyParticipants($task, $actorUserId, $payload);
        $this->notifyAssigned($task, $actorUserId, $newAssigneeUserId);
    }

    public function notifyClaimed(Task $task, string $actorUserId): void
    {
        $payload = $this->taskNotificationPayloadBuilder->buildClaimed($task);

        $this->notifyParticipants($task, $actorUserId, $payload);
    }

    private function notifyParticipants(Task $task, string $actorUserId, array $payload): void
    {
        $participantIds = $this->taskEvents
            ->getForTask((string) $task->id)
            ->pluck('actor_user_id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $candidateIds = array_filter(array_unique(array_merge(
            $participantIds,
            [
                (string) ($task->created_by_user_id ?? ''),
                (string) ($task->assigned_to_user_id ?? ''),
            ]
        )));

        $adminIds = $this->users->getUserIdsByRoles(['Administrator', 'admin']);

        $recipients = array_values(array_diff(
            array_unique(array_merge($candidateIds, $adminIds)),
            [(string) $actorUserId]
        ));

        $this->notifications->notifyUsers(
            recipientUserIds: $recipients,
            actorUserId: $actorUserId,
            type: $payload['type'],
            title: $payload['title'],
            message: $payload['message'],
            entityType: $payload['entity_type'],
            entityId: $payload['entity_id'],
            data: $payload['data'],
            moduleId: $task->module_id ? (string) $task->module_id : null,
            departmentId: $task->department_id ? (string) $task->department_id : null,
        );
    }
}
