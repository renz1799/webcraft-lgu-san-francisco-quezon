<?php

namespace App\Modules\Tasks\Builders;

use App\Modules\Tasks\Builders\Contracts\TaskNotificationPayloadBuilderInterface;
use App\Modules\Tasks\Models\Task;

class TaskNotificationPayloadBuilder implements TaskNotificationPayloadBuilderInterface
{
    public function buildAssigned(Task $task): array
    {
        return [
            'type' => 'task_assigned',
            'title' => 'New Task Assigned',
            'message' => "You were assigned: {$task->title}",
            'entity_type' => 'tasks',
            'entity_id' => (string) $task->id,
            'data' => [
                'task_id' => (string) $task->id,
                'task_title' => (string) $task->title,
                'url' => route('tasks.show', (string) $task->id),
            ],
        ];
    }

    public function buildStatusChanged(Task $task, string $fromStatus, string $toStatus): array
    {
        return [
            'type' => 'task_status_changed',
            'title' => 'Task Status Updated',
            'message' => "Task \"{$task->title}\" changed from {$fromStatus} to {$toStatus}.",
            'entity_type' => 'tasks',
            'entity_id' => (string) $task->id,
            'data' => [
                'task_id' => (string) $task->id,
                'url' => route('tasks.show', (string) $task->id),
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
            ],
        ];
    }

    public function buildReassigned(Task $task): array
    {
        return [
            'type' => 'task_reassigned',
            'title' => 'Task Reassigned',
            'message' => "Task \"{$task->title}\" was reassigned.",
            'entity_type' => 'tasks',
            'entity_id' => (string) $task->id,
            'data' => [
                'task_id' => (string) $task->id,
                'url' => route('tasks.show', (string) $task->id),
            ],
        ];
    }

    public function buildClaimed(Task $task): array
    {
        return [
            'type' => 'task_claimed',
            'title' => 'Task Claimed',
            'message' => "Task \"{$task->title}\" was claimed.",
            'entity_type' => 'tasks',
            'entity_id' => (string) $task->id,
            'data' => [
                'task_id' => (string) $task->id,
                'url' => route('tasks.show', (string) $task->id),
            ],
        ];
    }
}
