<?php

namespace App\Services\Contracts;

use App\Models\Inspection;
use App\Models\Notification;
use App\Models\Task;

interface NotificationServiceInterface
{
    public function notifyTaskAssigned(
        string $assigneeUserId,
        string $actorUserId,
        string $taskId,
        string $taskTitle
    ): Notification;

    public function notifyTaskParticipants(
        Task $task,
        string $actorUserId,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void;

    public function notifyInspectionSubmitted(
        Inspection $inspection,
        string $actorUserId,
        ?string $taskId = null
    ): void;
}
