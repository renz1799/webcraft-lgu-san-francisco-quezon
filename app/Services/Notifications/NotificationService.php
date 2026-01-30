<?php

namespace App\Services\Notifications;

use App\Models\Inspection;
use App\Models\Notification;
use App\Models\Task;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\NotificationServiceInterface;
use Carbon\Carbon;

class NotificationService implements NotificationServiceInterface
{
    public function __construct(
        private readonly NotificationRepositoryInterface $notifications,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly UserRepositoryInterface $users,
    ) {}

    public function notifyTaskAssigned(
        string $assigneeUserId,
        string $actorUserId,
        string $taskId,
        string $taskTitle,
        string $url
    ): Notification {
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
        // 1) Participants from task events (repo)
        $participantIds = $this->taskEvents
            ->getForTask((string) $task->id)
            ->pluck('actor_user_id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        // 2) Always include creator + assignee
        $candidateIds = array_filter(array_unique(array_merge(
            $participantIds,
            [
                (string) ($task->created_by_user_id ?? ''),
                (string) ($task->assigned_to_user_id ?? ''),
            ]
        )));

        // 3) Include admins (repo, no Eloquent calls here)
        $adminIds = $this->users->getUserIdsByRoles(['Administrator']);

        // 4) Final recipients: merge + dedupe + exclude actor
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
        Inspection $inspection,
        string $actorUserId,
        ?string $taskId = null
    ): void {
        // notify Administrator + Staff (repo)
        $recipients = $this->users->getUserIdsByRoles(['Administrator', 'Staff']);

        // exclude actor
        $recipients = array_values(array_diff($recipients, [(string) $actorUserId]));

        $po = trim((string) ($inspection->po_number ?? ''));
        $poLabel = $po !== '' ? " (PO No: {$po})" : "";

        $this->fanOutBulk(
            recipientUserIds: $recipients,
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
                'url'           => route('inspections.show', $inspection->id),
                'task_id'       => $taskId,
            ],
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
            if ($userId === '') continue;

            $rows[] = [
                'id'                => (string) \Illuminate\Support\Str::uuid(),
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
