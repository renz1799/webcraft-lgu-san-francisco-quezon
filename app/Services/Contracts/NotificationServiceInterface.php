<?php

namespace App\Services\Contracts;

use App\Models\Notification;
use App\Models\Task;

interface NotificationServiceInterface
{
    public function notifyUsersByRoles(
        array $roleNames,
        string $actorUserId,
        string $type,
        string $title,
        string $message,
        string $entityType,
        string $entityId,
        array $data = [],
        bool $excludeActor = true
    ): void;

    public function notifyTaskAssigned(
        string $assigneeUserId,
        string $actorUserId,
        string $taskId,
        string $taskTitle,
        ?string $url = null,
        ?string $moduleId = null,
        ?string $departmentId = null
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
        object $inspection,
        string $actorUserId,
        ?string $taskId = null
    ): void;
}
