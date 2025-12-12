<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaskAssignmentService
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {}

    public function assign(string $actorUserId, string $taskId, string $assigneeUserId): void
    {
        DB::transaction(function () use ($actorUserId, $taskId, $assigneeUserId) {
            /** @var Task|null $task */
            $task = Task::query()->find($taskId);

            if (!$task) {
                throw new ModelNotFoundException('Task not found.');
            }

            // Update assignment fields (adjust if your columns differ)
            $task->assigned_to_user_id = $assigneeUserId;
            $task->assigned_by_user_id = $actorUserId;
            $task->assigned_at = now();
            $task->save();

            // Notify assignee
            $this->notificationService->notifyTaskAssigned(
                assigneeUserId: $assigneeUserId,
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                taskTitle: (string) ($task->title ?? 'Task')
            );
        });
    }
}
