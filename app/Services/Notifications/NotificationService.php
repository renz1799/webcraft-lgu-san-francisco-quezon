<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\Notifications\NotificationServiceInterface;
use App\Support\CurrentContext;
use Carbon\Carbon;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly UserRepositoryInterface $users,
        private readonly CurrentContext $context,
    ) {}

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
    ): Notification {
        return $this->notifications->create([
            'module_id' => $moduleId ?: $this->context->moduleId(),
            'department_id' => $departmentId ?: $this->context->defaultDepartmentId(),
            'notifiable_user_id' => $notifiableUserId,
            'actor_user_id' => $actorUserId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'data' => $data,
        ]);
    }

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
    ): void {
        $recipients = array_values(array_unique(array_filter(
            array_map(static fn ($userId) => trim((string) $userId), $recipientUserIds)
        )));

        $this->fanOutBulk(
            recipientUserIds: $recipients,
            actorUserId: $actorUserId,
            type: $type,
            title: $title,
            message: $message,
            entityType: $entityType,
            entityId: $entityId,
            data: $data,
            moduleId: $moduleId ?: $this->context->moduleId(),
            departmentId: $departmentId ?: $this->context->defaultDepartmentId(),
        );
    }

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
    ): void {
        $roleNames = array_values(array_unique(array_filter(
            array_map(static fn ($roleName) => trim((string) $roleName), $roleNames)
        )));

        if ($roleNames === []) {
            return;
        }

        $recipients = array_values(array_unique($this->users->getUserIdsByRoles($roleNames)));

        if ($excludeActor) {
            $recipients = array_values(array_diff($recipients, [(string) $actorUserId]));
        }

        $this->notifyUsers(
            recipientUserIds: $recipients,
            actorUserId: $actorUserId,
            type: $type,
            title: $title,
            message: $message,
            entityType: $entityType,
            entityId: $entityId,
            data: $data,
            moduleId: $this->context->moduleId(),
            departmentId: $this->context->defaultDepartmentId(),
        );
    }

    private function fanOutBulk(
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
    ): void {
        $now = Carbon::now();

        $rows = [];
        foreach ($recipientUserIds as $userId) {
            $userId = (string) $userId;
            if ($userId === '') {
                continue;
            }

            $rows[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'module_id' => $moduleId,
                'department_id' => $departmentId,
                'notifiable_user_id' => $userId,
                'actor_user_id' => $actorUserId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'data' => json_encode($data),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->notifications->insertMany($rows);
    }
}
