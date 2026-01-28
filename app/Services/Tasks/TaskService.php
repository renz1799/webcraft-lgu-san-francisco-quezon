<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Services\Contracts\TaskServiceInterface;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TaskService implements TaskServiceInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly NotificationService $notificationService, // keep as concrete for now; can be interface later
    ) {}

    /**
     * Cache actor snapshots per request so we don't re-query multiple times.
     * @var array<string, array{actor_name_snapshot: string, actor_username_snapshot: ?string}>
     */
    private array $actorSnapshotCache = [];

    /**
     * Build immutable actor "snapshot" fields for audit/history.
     */
    private function actorSnapshots(string $actorUserId): array
    {
        if (isset($this->actorSnapshotCache[$actorUserId])) {
            return $this->actorSnapshotCache[$actorUserId];
        }

        $actor = User::with('profile')->find($actorUserId);

        $actorName = $actor?->profile?->full_name
            ?: ($actor?->username ?: 'Unknown User');

        return $this->actorSnapshotCache[$actorUserId] = [
            'actor_name_snapshot' => $actorName,
            'actor_username_snapshot' => $actor?->username,
        ];
    }

    /**
     * Always create TaskEvent with snapshot columns included.
     */
    private function createEvent(array $payload): void
    {
        $actorUserId = (string) ($payload['actor_user_id'] ?? '');
        if ($actorUserId === '') {
            throw new InvalidArgumentException('actor_user_id is required when creating task events.');
        }

        $this->taskEvents->create(array_merge(
            $payload,
            $this->actorSnapshots($actorUserId)
        ));
    }

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

            // event: created
            $this->createEvent([
                'task_id' => $task->id,
                'actor_user_id' => $actorUserId,
                'event_type' => 'created',
                'note' => 'Task created.',
            ]);

            // event: assigned
            $this->createEvent([
                'task_id' => $task->id,
                'actor_user_id' => $actorUserId,
                'event_type' => 'assigned',
                'note' => 'Task assigned.',
                'meta' => [
                    'to_user_id' => $assigneeUserId,
                ],
            ]);

            // notify assignee (notification links to TASK, not subject)
            $this->notificationService->notifyTaskAssigned(
                assigneeUserId: $assigneeUserId,
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                taskTitle: $task->title
            );

            return $task;
        });
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

        if (!in_array($toStatus, $allowed, true)) {
            throw new InvalidArgumentException('Invalid task status.');
        }

        return DB::transaction(function () use ($actorUserId, $taskId, $toStatus, $note) {
            $task = $this->tasks->findOrFail($taskId);

            $fromStatus = (string) $task->status;

            if ($fromStatus === $toStatus) {
                // still allow a note-only event even if status unchanged
                if ($note) {
                    $this->createEvent([
                        'task_id' => $task->id,
                        'actor_user_id' => $actorUserId,
                        'event_type' => 'comment',
                        'note' => $note,
                    ]);
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

            $this->createEvent([
                'task_id' => $task->id,
                'actor_user_id' => $actorUserId,
                'event_type' => 'status_changed',
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note,
            ]);

            // ✅ notify: admin + creator + assignee + participants (no spam on comments)
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

            $this->createEvent([
                'task_id' => $task->id,
                'actor_user_id' => $actorUserId,
                'event_type' => 'comment',
                'note' => $note,
            ]);
        });
    }

    public function reassign(
        string $actorUserId,
        string $taskId,
        string $newAssigneeUserId,
        ?string $note = null
    ): Task {
        return DB::transaction(function () use ($actorUserId, $taskId, $newAssigneeUserId, $note) {
            $task = $this->tasks->findOrFail($taskId);

            $oldAssignee = $task->assigned_to_user_id;

            $task->assigned_to_user_id = $newAssigneeUserId;
            $this->tasks->save($task);

            $this->createEvent([
                'task_id' => $task->id,
                'actor_user_id' => $actorUserId,
                'event_type' => 'reassigned',
                'note' => $note,
                'meta' => [
                    'from_user_id' => $oldAssignee,
                    'to_user_id' => $newAssigneeUserId,
                ],
            ]);

            $this->notificationService->notifyTaskParticipants(
                task: $task,
                actorUserId: $actorUserId,
                type: 'task_reassigned',
                title: 'Task Reassigned',
                message: "Task \"{$task->title}\" was reassigned."
            );

            // notify new assignee (direct)
            $this->notificationService->notifyTaskAssigned(
                assigneeUserId: $newAssigneeUserId,
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                taskTitle: $task->title
            );

            return $task;
        });
    }

    public function claim(string $actorUserId, string $taskId, ?string $note = null): Task
    {
        return DB::transaction(function () use ($actorUserId, $taskId, $note) {
            $task = $this->tasks->findOrFail($taskId);

            if (!empty($task->assigned_to_user_id)) {
                return $task; // already claimed/assigned
            }

            $task->assigned_to_user_id = $actorUserId;
            $this->tasks->save($task);

            $this->createEvent([
                'task_id' => $task->id,
                'actor_user_id' => $actorUserId,
                'event_type' => 'claimed',
                'note' => $note ?? 'Task claimed.',
                'meta' => [
                    'claimed_by_user_id' => $actorUserId,
                ],
            ]);

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

    public function tableData(string $actorUserId, array $roles, array $filters, int $page, int $size): array
    {
        $paginator = $this->tasks->paginateForTable(
            userId: $actorUserId,
            roles: $roles,
            filters: $filters,
            page: $page,
            size: $size
        );

        return [
            'ok' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }

}
