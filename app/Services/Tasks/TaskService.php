<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\Contracts\TaskServiceInterface;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TaskService implements TaskServiceInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly NotificationService $notificationService,
    ) {}

    public function createAndAssign(
        string $actorUserId,
        string $assigneeUserId,
        string $title,
        ?string $description = null,
        ?string $type = null,
        ?string $subjectType = null,
        ?string $subjectId = null,
        array $data = []
    ): Task {
        return DB::transaction(function () use (
            $actorUserId,
            $assigneeUserId,
            $title,
            $description,
            $type,
            $subjectType,
            $subjectId,
            $data
        ) {
            $task = $this->tasks->create([
                'title' => $title,
                'description' => $description,
                'type' => $type,
                'status' => Task::STATUS_PENDING,
                'created_by_user_id' => $actorUserId,
                'assigned_to_user_id' => $assigneeUserId,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'data' => $data,
            ]);

            $this->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'created',
                note: 'Task created.'
            );

            $this->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'assigned',
                note: 'Task assigned.',
                meta: [
                    'to_user_id' => $assigneeUserId,
                ]
            );

            $this->notificationService->notifyTaskAssigned(
                assigneeUserId: $assigneeUserId,
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                taskTitle: $task->title
            );

            return $task;
        });
    }

    public function createUnassigned(
        string $actorUserId,
        string $title,
        ?string $description = null,
        ?string $type = null,
        ?string $subjectType = null,
        ?string $subjectId = null,
        array $data = []
    ): Task {
        return DB::transaction(function () use (
            $actorUserId,
            $title,
            $description,
            $type,
            $subjectType,
            $subjectId,
            $data
        ) {
            $task = $this->tasks->create([
                'title' => $title,
                'description' => $description,
                'type' => $type,
                'status' => Task::STATUS_PENDING,
                'created_by_user_id' => $actorUserId,
                'assigned_to_user_id' => null,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'data' => $data,
            ]);

            $this->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'created',
                note: 'Task created.'
            );

            return $task;
        });
    }

    public function findLatestBySubject(string $subjectType, string $subjectId): ?Task
    {
        return $this->tasks->findLatestBySubject($subjectType, $subjectId);
    }

    public function updateTaskAssignmentAndData(
        string $taskId,
        ?string $assignedToUserId,
        array $data
    ): Task {
        return DB::transaction(function () use ($taskId, $assignedToUserId, $data) {
            $task = $this->tasks->findOrFail($taskId);

            $task->assigned_to_user_id = $assignedToUserId;
            $task->data = $data;

            return $this->tasks->save($task);
        });
    }

    public function recordEvent(
        string $actorUserId,
        string $taskId,
        string $eventType,
        ?string $note = null,
        array $meta = [],
        ?string $fromStatus = null,
        ?string $toStatus = null
    ): void {
        if ($actorUserId === '' || $taskId === '' || $eventType === '') {
            throw new InvalidArgumentException('actorUserId, taskId, and eventType are required.');
        }

        $this->taskEvents->create([
            'task_id' => $taskId,
            'actor_user_id' => $actorUserId,
            'event_type' => $eventType,
            'note' => $note,
            'meta' => $meta,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ]);
    }

    public function changeStatus(
        string $actorUserId,
        string $taskId,
        string $toStatus,
        ?string $note = null
    ): Task {
        $allowed = [
            Task::STATUS_PENDING,
            Task::STATUS_IN_PROGRESS,
            Task::STATUS_DONE,
            Task::STATUS_CANCELLED,
        ];

        if (! in_array($toStatus, $allowed, true)) {
            throw new InvalidArgumentException('Invalid task status.');
        }

        return DB::transaction(function () use ($actorUserId, $taskId, $toStatus, $note) {
            $task = $this->tasks->findOrFail($taskId);
            $fromStatus = (string) $task->status;

            if ($fromStatus === $toStatus) {
                if ($note) {
                    $this->recordEvent(
                        actorUserId: $actorUserId,
                        taskId: (string) $task->id,
                        eventType: 'comment',
                        note: $note
                    );
                }

                return $task;
            }

            $task->status = $toStatus;

            if ($toStatus === Task::STATUS_IN_PROGRESS) {
                $task->started_at = $task->started_at ?? now();
            }

            if ($toStatus === Task::STATUS_DONE) {
                $task->completed_at = now();
            }

            if ($toStatus === Task::STATUS_CANCELLED) {
                $task->cancelled_at = now();
            }

            $this->tasks->save($task);

            $this->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'status_changed',
                note: $note,
                fromStatus: $fromStatus,
                toStatus: $toStatus
            );

            $this->notificationService->notifyTaskParticipants(
                task: $task,
                actorUserId: $actorUserId,
                type: 'task_status_changed',
                title: 'Task Status Updated',
                message: "Task \"{$task->title}\" changed from {$fromStatus} to {$toStatus}.",
                data: [
                    'from_status' => $fromStatus,
                    'to_status' => $toStatus,
                ]
            );

            return $task;
        });
    }

    public function addComment(string $actorUserId, string $taskId, string $note): void
    {
        DB::transaction(function () use ($actorUserId, $taskId, $note) {
            $task = $this->tasks->findOrFail($taskId);

            $this->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'comment',
                note: $note
            );
        });
    }

    public function reassign(
        string $actorUserId,
        string $taskId,
        string $newAssigneeUserId,
        ?string $note = null
    ): Task {
        return DB::transaction(function () use (
            $actorUserId,
            $taskId,
            $newAssigneeUserId,
            $note
        ) {
            $task = $this->tasks->findOrFail($taskId);

            $fromUserId = $task->assigned_to_user_id;
            $fromUser = $fromUserId ? User::with('profile')->find($fromUserId) : null;
            $toUser = User::with('profile')->find($newAssigneeUserId);

            $fromName = $fromUser?->profile?->full_name
                ?? $fromUser?->username
                ?? 'Unassigned';

            $toName = $toUser?->profile?->full_name
                ?? $toUser?->username
                ?? 'Unassigned';

            $task->assigned_to_user_id = $newAssigneeUserId;
            $this->tasks->save($task);

            $customNote = trim((string) $note);
            $lines = ["Task reassigned: {$fromName} -> {$toName}."];

            if ($customNote !== '') {
                $lines[] = "Note: {$customNote}";
            }

            $finalNote = implode("\n", $lines);

            $this->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'task_reassigned',
                note: $finalNote,
                meta: [
                    'from_user_id' => $fromUserId,
                    'to_user_id' => $newAssigneeUserId,
                    'custom_note' => $customNote !== '' ? $customNote : null,
                ],
                fromStatus: (string) $task->status,
                toStatus: (string) $task->status
            );

            $this->notificationService->notifyTaskParticipants(
                task: $task,
                actorUserId: $actorUserId,
                type: 'task_reassigned',
                title: 'Task Reassigned',
                message: "Task \"{$task->title}\" was reassigned."
            );

            $this->notificationService->notifyTaskAssigned(
                assigneeUserId: (string) $newAssigneeUserId,
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                taskTitle: (string) $task->title,
                url: route('tasks.show', (string) $task->id)
            );

            return $task;
        });
    }

    public function claim(string $actorUserId, string $taskId, ?string $note = null): Task
    {
        return DB::transaction(function () use ($actorUserId, $taskId, $note) {
            $task = $this->tasks->findOrFail($taskId);

            if (! empty($task->assigned_to_user_id)) {
                return $task;
            }

            $task->assigned_to_user_id = $actorUserId;
            $this->tasks->save($task);

            $this->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'claimed',
                note: $note ?? 'Task claimed.',
                meta: [
                    'claimed_by_user_id' => $actorUserId,
                ]
            );

            $this->notificationService->notifyTaskParticipants(
                task: $task,
                actorUserId: $actorUserId,
                type: 'task_claimed',
                title: 'Task Claimed',
                message: "Task \"{$task->title}\" was claimed."
            );

            return $task;
        });
    }

    public function archive(string $actorUserId, string $taskId): void
    {
        DB::transaction(function () use ($actorUserId, $taskId) {
            $task = $this->tasks->findOrFail($taskId);

            if ($task->trashed()) {
                return;
            }

            $this->tasks->delete($task);

            $this->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'archived',
                note: 'Task archived.'
            );
        });
    }

    public function restore(string $actorUserId, string $taskId): bool
    {
        return DB::transaction(function () use ($actorUserId, $taskId) {
            $task = $this->tasks->findOrFailWithTrashed($taskId);

            if (! $task->trashed()) {
                return true;
            }

            $ok = $this->tasks->restore($task);
            if (! $ok) {
                return false;
            }

            $this->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'restored',
                note: 'Task restored.'
            );

            return true;
        });
    }

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));

        $filters = $params;
        unset($filters['page'], $filters['size']);

        return $this->tasks->datatable($filters, $page, $size);
    }
}
