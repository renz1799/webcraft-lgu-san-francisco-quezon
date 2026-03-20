<?php

namespace App\Services\Contracts;

use App\Models\Notification;

interface NotificationServiceInterface
{
    public function notifyUser(
        string $notifiableUserId,
        string $actorUserId,
        string $type,
        string $title,
        string $message,
        string $entityType,
        string $entityId,
        array $data = [],
        ?string $moduleId = null,
        ?string $departmentId = null
    ): Notification;

    public function notifyUsers(
        array $recipientUserIds,
        string $actorUserId,
        string $type,
        string $title,
        string $message,
        string $entityType,
        string $entityId,
        array $data = [],
        ?string $moduleId = null,
        ?string $departmentId = null
    ): void;

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
}
