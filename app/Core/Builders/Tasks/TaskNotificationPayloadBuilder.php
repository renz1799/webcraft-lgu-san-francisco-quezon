<?php

namespace App\Core\Builders\Tasks;

use App\Core\Builders\Tasks\Contracts\TaskNotificationPayloadBuilderInterface;
use App\Core\Models\Module;
use App\Core\Models\Tasks\Task;
use Illuminate\Support\Facades\Route;

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
                'url' => $this->taskShowUrl($task),
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
                'url' => $this->taskShowUrl($task),
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
                'url' => $this->taskShowUrl($task),
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
                'url' => $this->taskShowUrl($task),
            ],
        ];
    }

    private function taskShowUrl(Task $task): string
    {
        $routeName = $this->taskShowRouteName($task);

        return route($routeName, ['id' => (string) $task->id]);
    }

    private function taskShowRouteName(Task $task): string
    {
        $moduleCode = strtoupper(trim((string) ($task->module?->code ?? '')));

        if ($moduleCode === '') {
            $moduleId = trim((string) ($task->module_id ?? ''));

            if ($moduleId !== '') {
                $moduleCode = strtoupper((string) (Module::query()->whereKey($moduleId)->value('code') ?? ''));
            }
        }

        if ($moduleCode !== '') {
            $moduleRouteName = strtolower($moduleCode) . '.tasks.show';

            if (Route::has($moduleRouteName)) {
                return $moduleRouteName;
            }
        }

        return 'tasks.show';
    }
}
