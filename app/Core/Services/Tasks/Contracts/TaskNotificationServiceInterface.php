<?php

namespace App\Core\Services\Tasks\Contracts;

use App\Core\Models\Notification;
use App\Core\Models\Tasks\Task;

interface TaskNotificationServiceInterface
{
    public function notifyAssigned(Task $task, string $actorUserId, string $assigneeUserId): Notification;

    public function notifyStatusChanged(Task $task, string $actorUserId, string $fromStatus, string $toStatus): void;

    public function notifyReassigned(Task $task, string $actorUserId, string $newAssigneeUserId): void;

    public function notifyClaimed(Task $task, string $actorUserId): void;
}
