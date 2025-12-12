<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications
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

            // subject / entity reference
            'entity_type' => 'tasks',
            'entity_id' => $taskId,

            // flexible payload (URL is optional but very convenient)
            'data' => [
                'task_id' => $taskId,
                'task_title' => $taskTitle,
                // store as relative URL; keep it stable
                'url' => route('tasks.show', $taskId),
            ],
        ]);
    }
}
