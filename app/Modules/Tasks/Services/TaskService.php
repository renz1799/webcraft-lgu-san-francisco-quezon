<?php

namespace App\Modules\Tasks\Services;

use App\Modules\Tasks\Builders\Contracts\TaskReassignmentNoteBuilderInterface;
use App\Modules\Tasks\Models\Task;
use App\Core\Models\User;
use App\Modules\Tasks\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Modules\Tasks\Repositories\Contracts\TaskRepositoryInterface;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Modules\Tasks\Services\Contracts\TaskServiceInterface;
use App\Modules\Tasks\Services\Contracts\TaskNotificationServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TaskService implements TaskServiceInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly UserRepositoryInterface $users,
        private readonly TaskNotificationServiceInterface $taskNotifications,
        private readonly CurrentContext $context,
        private readonly ModuleDepartmentResolverInterface $moduleDepartments,
        private readonly TaskReassignmentNoteBuilderInterface $taskReassignmentNoteBuilder,
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
            $moduleId = $this->requireModuleId();
            $departmentId = $this->moduleDepartments->resolveForModule($moduleId);
            $assignee = $this->findActiveCurrentModuleUserOrFail($assigneeUserId);

            $task = $this->tasks->create([
                'module_id' => $moduleId,
                'department_id' => $departmentId,
                'title' => $title,
                'description' => $description,
                'type' => $type,
                'status' => Task::STATUS_PENDING,
                'created_by_user_id' => $actorUserId,
                'assigned_to_user_id' => (string) $assignee->id,
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
                    'to_user_id' => (string) $assignee->id,
                ]
            );

            $this->taskNotifications->notifyAssigned(
                task: $task,
                actorUserId: $actorUserId,
                assigneeUserId: (string) $assignee->id,
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
            $moduleId = $this->requireModuleId();

            $task = $this->tasks->create([
                'module_id' => $moduleId,
                'department_id' => $this->moduleDepartments->resolveForModule($moduleId),
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
        $moduleId = $this->context->moduleId();

        if (! $moduleId) {
            return null;
        }

        return $this->tasks->findLatestBySubject($subjectType, $subjectId, $moduleId);
    }

    public function updateTaskAssignmentAndData(
        string $taskId,
        ?string $assignedToUserId,
        array $data
    ): Task {
        return $this->syncTaskContext(
            taskId: $taskId,
            data: $data,
            assignmentMode: 'set',
            assignedToUserId: $assignedToUserId,
            title: null,
            description: null,
            type: null,
            mergeData: false
        );
    }

    public function syncTaskContext(
        string $taskId,
        array $data,
        string $assignmentMode = 'keep',
        ?string $assignedToUserId = null,
        ?string $title = null,
        ?string $description = null,
        ?string $type = null,
        bool $mergeData = true
    ): Task {
        return DB::transaction(function () use (
            $taskId,
            $data,
            $assignmentMode,
            $assignedToUserId,
            $title,
            $description,
            $type,
            $mergeData
        ) {
            if (! in_array($assignmentMode, ['keep', 'set', 'clear'], true)) {
                throw new InvalidArgumentException('Invalid assignment mode. Allowed values: keep, set, clear.');
            }

            $task = $this->findTaskOrFail($taskId);

            $targetData = $mergeData
                ? array_replace_recursive((array) ($task->data ?? []), $data)
                : $data;

            $targetAssignee = match ($assignmentMode) {
                'keep' => $task->assigned_to_user_id,
                'set' => $assignedToUserId ? (string) $this->findActiveCurrentModuleUserOrFail($assignedToUserId)->id : null,
                'clear' => null,
            };

            $task->assigned_to_user_id = $targetAssignee;
            $task->data = $targetData;

            if ($title !== null) {
                $task->title = $title;
            }

            if ($description !== null) {
                $task->description = $description;
            }

            if ($type !== null) {
                $task->type = $type;
            }

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
            $task = $this->findTaskOrFail($taskId);
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

            $this->taskNotifications->notifyStatusChanged(
                task: $task,
                actorUserId: $actorUserId,
                fromStatus: $fromStatus,
                toStatus: $toStatus,
            );

            return $task;
        });
    }

    public function addComment(string $actorUserId, string $taskId, string $note): void
    {
        DB::transaction(function () use ($actorUserId, $taskId, $note) {
            $task = $this->findTaskOrFail($taskId);

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
            $task = $this->findTaskOrFail($taskId);

            $fromUser = $this->findCurrentModuleUser($task->assigned_to_user_id);
            $toUser = $this->findActiveCurrentModuleUserOrFail($newAssigneeUserId);

            $task->assigned_to_user_id = (string) $toUser->id;
            $this->tasks->save($task);

            $reassignment = $this->taskReassignmentNoteBuilder->build($fromUser, $toUser, $note);

            $this->recordEvent(
                actorUserId: $actorUserId,
                taskId: (string) $task->id,
                eventType: 'task_reassigned',
                note: $reassignment['note'],
                meta: $reassignment['meta'],
                fromStatus: (string) $task->status,
                toStatus: (string) $task->status
            );

            $this->taskNotifications->notifyReassigned(
                task: $task,
                actorUserId: $actorUserId,
                newAssigneeUserId: (string) $toUser->id,
            );

            return $task;
        });
    }

    public function claim(string $actorUserId, string $taskId, ?string $note = null): Task
    {
        return DB::transaction(function () use ($actorUserId, $taskId, $note) {
            $task = $this->findTaskOrFail($taskId);

            if (! empty($task->assigned_to_user_id)) {
                return $task;
            }

            $this->findActiveCurrentModuleUserOrFail($actorUserId);

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

            $this->taskNotifications->notifyClaimed($task, $actorUserId);

            return $task;
        });
    }

    public function archive(string $actorUserId, string $taskId): void
    {
        DB::transaction(function () use ($actorUserId, $taskId) {
            $task = $this->findTaskOrFail($taskId);

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
            $task = $this->findTaskOrFailWithTrashed($taskId);

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

    private function findTaskOrFail(string $taskId): Task
    {
        return $this->tasks->findOrFail($taskId, $this->requireModuleId());
    }

    private function findTaskOrFailWithTrashed(string $taskId): Task
    {
        return $this->tasks->findOrFailWithTrashed($taskId, $this->requireModuleId());
    }

    private function requireModuleId(): string
    {
        $moduleId = (string) ($this->context->moduleId() ?? '');

        if ($moduleId !== '') {
            return $moduleId;
        }

        $exception = new ModelNotFoundException();
        $exception->setModel(Task::class);

        throw $exception;
    }

    private function findCurrentModuleUser(?string $userId): ?User
    {
        $userId = trim((string) $userId);

        if ($userId === '') {
            return null;
        }

        return $this->users->findInModule($userId, $this->requireModuleId());
    }

    private function findActiveCurrentModuleUserOrFail(string $userId): User
    {
        $user = $this->users->findActiveInModule($userId, $this->requireModuleId());

        if ($user) {
            return $user;
        }

        throw new InvalidArgumentException('Assignee is not available in the current module.');
    }
}
