<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\Task;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\NotificationServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly UserRepositoryInterface $users,
    ) {}

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

        $this->fanOutBulk(
            recipientUserIds: $recipients,
            actorUserId: $actorUserId,
            type: $type,
            title: $title,
            message: $message,
            entityType: $entityType,
            entityId: $entityId,
            data: $data,
        );
    }

    public function notifyTaskAssigned(
        string $assigneeUserId,
        string $actorUserId,
        string $taskId,
        string $taskTitle,
        ?string $url = null
    ): Notification {
        $url ??= route('tasks.show', $taskId);

        return $this->notifications->create([
            'notifiable_user_id' => $assigneeUserId,
            'actor_user_id'      => $actorUserId,
            'type'               => 'task_assigned',
            'title'              => 'New Task Assigned',
            'message'            => "You were assigned: {$taskTitle}",
            'entity_type'        => 'tasks',
            'entity_id'          => $taskId,
            'data'               => [
                'task_id'    => $taskId,
                'task_title' => $taskTitle,
                'url'        => $url,
            ],
        ]);
    }

    /**
     * Notify relevant people:
     * - admins
     * - task creator
     * - current assignee
     * - participants (task event actors)
     *
     * One notification row per recipient (read state per-user).
     */
    public function notifyTaskParticipants(
        Task $task,
        string $actorUserId,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        $participantIds = $this->taskEvents
            ->getForTask((string) $task->id)
            ->pluck('actor_user_id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $candidateIds = array_filter(array_unique(array_merge(
            $participantIds,
            [
                (string) ($task->created_by_user_id ?? ''),
                (string) ($task->assigned_to_user_id ?? ''),
            ]
        )));

        $adminIds = $this->users->getUserIdsByRoles(['Administrator']);

        $recipients = array_values(array_diff(
            array_unique(array_merge($candidateIds, $adminIds)),
            [(string) $actorUserId]
        ));

        $this->fanOutBulk(
            recipientUserIds: $recipients,
            actorUserId: $actorUserId,
            type: $type,
            title: $title,
            message: $message,
            entityType: 'tasks',
            entityId: (string) $task->id,
            data: array_merge($data, [
                'task_id' => (string) $task->id,
                'url'     => route('tasks.show', $task->id),
            ]),
        );
    }

    public function notifyInspectionSubmitted(
        object $inspection,
        string $actorUserId,
        ?string $taskId = null
    ): void {
        $po = trim((string) ($inspection->po_number ?? ''));
        $poLabel = $po !== '' ? " (PO No: {$po})" : '';
        $url = $inspection->url ?? null;

        if ($url === null && Route::has('inspections.show')) {
            $url = route('inspections.show', $inspection->id);
        }

        $this->notifyUsersByRoles(
            roleNames: ['Administrator', 'Staff'],
            actorUserId: $actorUserId,
            type: 'inspection_submitted',
            title: 'Inspection Submitted',
            message: "Inspection submitted for review{$poLabel}.",
            entityType: 'inspections',
            entityId: (string) $inspection->id,
            data: [
                'inspection_id' => (string) $inspection->id,
                'po_number'     => $inspection->po_number,
                'dv_number'     => $inspection->dv_number,
                'status'        => $inspection->status,
                'url'           => $url,
                'task_id'       => $taskId,
            ],
            excludeActor: true,
        );
    }

    /**
     * Bulk fan-out (single insert query) to avoid N inserts.
     */
    private function fanOutBulk(
        array $recipientUserIds,
        string $actorUserId,
        string $type,
        string $title,
        string $message,
        string $entityType,
        string $entityId,
        array $data = []
    ): void {
        $now = Carbon::now();

        $rows = [];
        foreach ($recipientUserIds as $userId) {
            $userId = (string) $userId;
            if ($userId === '') {
                continue;
            }

            $rows[] = [
                'id'                 => (string) \Illuminate\Support\Str::uuid(),
                'notifiable_user_id' => $userId,
                'actor_user_id'      => $actorUserId,
                'type'               => $type,
                'title'              => $title,
                'message'            => $message,
                'entity_type'        => $entityType,
                'entity_id'          => $entityId,
                'data'               => json_encode($data),
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        $this->notifications->insertMany($rows);
    }
}