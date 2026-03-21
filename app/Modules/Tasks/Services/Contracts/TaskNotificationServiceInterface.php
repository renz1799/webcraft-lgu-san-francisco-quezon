<?php

namespace App\Modules\Tasks\Services\Contracts;

use App\Core\Models\Notification;
use App\Modules\Tasks\Models\Task;

interface TaskNotificationServiceInterface
{
    public function notifyAssigned(Task $task, string $actorUserId, string $assigneeUserId): Notification;

    public function notifyStatusChanged(Task $task, string $actorUserId, string $fromStatus, string $toStatus): void;

    public function notifyReassigned(Task $task, string $actorUserId, string $newAssigneeUserId): void;

    public function notifyClaimed(Task $task, string $actorUserId): void;
}
