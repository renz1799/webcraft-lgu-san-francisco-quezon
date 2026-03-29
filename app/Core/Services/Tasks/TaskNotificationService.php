<?php

namespace App\Core\Services\Tasks;

use App\Core\Builders\Tasks\Contracts\TaskNotificationPayloadBuilderInterface;
use App\Core\Models\Notification;
use App\Core\Models\Tasks\Task;
use App\Core\Repositories\Tasks\Contracts\TaskEventRepositoryInterface;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Contracts\Notifications\NotificationServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskNotificationServiceInterface;

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
        $excludeCreator = (string) ($task->type ?? '') === 'identity_change_review';

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
                $excludeCreator ? '' : (string) ($task->created_by_user_id ?? ''),
                (string) ($task->assigned_to_user_id ?? ''),
            ]
        )));

        $adminIds = $task->module_id
            ? $this->users->getUserIdsByRolesInModule(['Administrator', 'admin'], (string) $task->module_id)
            : [];

        $recipients = array_values(array_diff(
            array_unique(array_merge($candidateIds, $adminIds)),
            array_filter([
                (string) $actorUserId,
                $excludeCreator ? (string) ($task->created_by_user_id ?? '') : '',
            ])
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
