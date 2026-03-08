<?php

namespace App\Services\Tasks;

use App\Models\Task;
use App\Services\Contracts\TaskServiceInterface;
use App\Services\Contracts\TaskTimelineServiceInterface;
use Illuminate\Support\Arr;

class TaskTimelineService implements TaskTimelineServiceInterface
{
    public function __construct(
        private readonly TaskServiceInterface $tasks,
    ) {}

    public function findLatestBySubject(string $subjectType, string $subjectId): ?Task
    {
        return $this->tasks->findLatestBySubject($subjectType, $subjectId);
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
        return $this->tasks->createUnassigned(
            actorUserId: $actorUserId,
            title: $title,
            description: $description,
            type: $type,
            subjectType: $subjectType,
            subjectId: $subjectId,
            data: $data
        );
    }

    public function updateTaskAssignmentAndData(
        string $taskId,
        ?string $assignedToUserId,
        array $data
    ): Task {
        return $this->tasks->updateTaskAssignmentAndData($taskId, $assignedToUserId, $data);
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
        $this->tasks->recordEvent(
            actorUserId: $actorUserId,
            taskId: $taskId,
            eventType: $eventType,
            note: $note,
            meta: $meta,
            fromStatus: $fromStatus,
            toStatus: $toStatus
        );
    }

    public function recordIfChanged(
        string $actorUserId,
        string $subjectType,
        string $subjectId,
        array $data,
        ?string $assignedToUserId = null,
        ?string $title = null,
        ?string $description = null,
        ?string $type = null,
        ?string $note = null
    ): Task {
        $task = $this->tasks->findLatestBySubject($subjectType, $subjectId);

        if (! $task) {
            $task = $this->tasks->createUnassigned(
                actorUserId: $actorUserId,
                title: $this->resolveInitialTitle($subjectType, $subjectId, $title),
                description: $description,
                type: $type,
                subjectType: $subjectType,
                subjectId: $subjectId,
                data: $data,
            );

            if ($assignedToUserId !== null) {
                $task = $this->tasks->updateTaskAssignmentAndData(
                    taskId: (string) $task->id,
                    assignedToUserId: $assignedToUserId,
                    data: $data,
                );

                $this->tasks->recordEvent(
                    actorUserId: $actorUserId,
                    taskId: (string) $task->id,
                    eventType: 'assigned',
                    note: $note ?? 'Task assigned from timeline context.',
                    meta: [
                        'subject_type' => $subjectType,
                        'subject_id' => $subjectId,
                        'to_user_id' => $assignedToUserId,
                    ],
                );
            }

            return $task;
        }

        $previousData = (array) ($task->data ?? []);
        $targetAssignee = $assignedToUserId ?? $task->assigned_to_user_id;

        $changedDataKeys = $this->diffDataKeys($previousData, $data);
        $assigneeChanged = (string) ($task->assigned_to_user_id ?? '') !== (string) ($targetAssignee ?? '');

        if ($changedDataKeys === [] && ! $assigneeChanged) {
            return $task;
        }

        $updatedTask = $this->tasks->updateTaskAssignmentAndData(
            taskId: (string) $task->id,
            assignedToUserId: $targetAssignee,
            data: $data,
        );

        $this->tasks->recordEvent(
            actorUserId: $actorUserId,
            taskId: (string) $updatedTask->id,
            eventType: 'timeline_context_updated',
            note: $note ?? 'Task timeline context updated.',
            meta: [
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'changed_data_keys' => $changedDataKeys,
                'assignee_changed' => $assigneeChanged,
                'from_assigned_to_user_id' => $task->assigned_to_user_id,
                'to_assigned_to_user_id' => $targetAssignee,
            ],
            fromStatus: (string) $task->status,
            toStatus: (string) $updatedTask->status,
        );

        return $updatedTask;
    }

    private function resolveInitialTitle(string $subjectType, string $subjectId, ?string $title): string
    {
        $trimmed = trim((string) $title);
        if ($trimmed !== '') {
            return $trimmed;
        }

        return sprintf('Workflow task for %s %s', $subjectType, $subjectId);
    }

    private function diffDataKeys(array $before, array $after): array
    {
        $beforeFlat = Arr::dot($before);
        $afterFlat = Arr::dot($after);

        $keys = array_unique(array_merge(array_keys($beforeFlat), array_keys($afterFlat)));
        sort($keys);

        $changed = [];
        foreach ($keys as $key) {
            if (($beforeFlat[$key] ?? null) !== ($afterFlat[$key] ?? null)) {
                $changed[] = (string) $key;
            }
        }

        return $changed;
    }
}
