<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\User;
use App\Models\Task;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\TaskEventRepositoryInterface;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly TaskEventRepositoryInterface $taskEvents,
    ) {}

    public function notifyTaskAssigned(
        string $assigneeUserId,
        string $actorUserId,
        string $taskId,
        string $taskTitle
    ): Notification {
        return $this->notifications->create([
            'notifiable_user_id' => $assigneeUserId,
            'actor_user_id' => $actorUserId,
            'type' => 'task_assigned',
            'title' => 'New Task Assigned',
            'message' => "You were assigned: {$taskTitle}",

            // entity reference
            'entity_type' => 'tasks',
            'entity_id' => $taskId,

            'data' => [
                'task_id' => $taskId,
                'task_title' => $taskTitle,
                'url' => route('tasks.show', $taskId),
            ],
        ]);
    }

    /**
     * Notify relevant people for important changes:
     * - admins
     * - task creator
     * - current assignee
     * - participants (people who acted on the task before)
     *
     * Creates one notification row per recipient (so read state is per-user).
     */
    public function notifyTaskParticipants(
        Task $task,
        string $actorUserId,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        // 1) Participants from task events
        $participantIds = $this->taskEvents
            ->getForTask((string) $task->id)
            ->pluck('actor_user_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        // 2) Always include creator + assignee
        $candidateIds = array_filter(array_unique(array_merge(
            $participantIds,
            [
                (string) ($task->created_by_user_id ?? ''),
                (string) ($task->assigned_to_user_id ?? ''),
            ]
        )));

        // 3) Include admins (Spatie roles)
        $adminIds = User::role('admin')->pluck('id')->map(fn ($id) => (string) $id)->all();

        // 4) Final recipients: merge + dedupe + exclude actor
        $recipients = array_values(array_diff(
            array_unique(array_merge($candidateIds, $adminIds)),
            [(string) $actorUserId]
        ));

        // 5) Fan-out
        $this->fanOut(
            recipientUserIds: $recipients,
            actorUserId: $actorUserId,
            type: $type,
            title: $title,
            message: $message,
            entityType: 'tasks',
            entityId: (string) $task->id,
            data: array_merge($data, [
                'task_id' => (string) $task->id,
                'url' => route('tasks.show', $task->id),
            ]),
        );
    }

    private function fanOut(
        array $recipientUserIds,
        string $actorUserId,
        string $type,
        string $title,
        string $message,
        string $entityType,
        string $entityId,
        array $data = []
    ): void {
        foreach ($recipientUserIds as $userId) {
            $userId = (string) $userId;
            if ($userId === '') continue;

            $this->notifications->create([
                'notifiable_user_id' => $userId,
                'actor_user_id' => $actorUserId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'data' => $data,
            ]);
        }
    }
}
