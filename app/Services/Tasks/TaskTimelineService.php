<?php

namespace App\Services\Tasks;

use App\Builders\Contracts\Tasks\TaskTimelineContextMetaBuilderInterface;
use App\Models\Task;
use App\Services\Contracts\TaskServiceInterface;
use App\Services\Contracts\TaskTimelineServiceInterface;
use InvalidArgumentException;

class TaskTimelineService implements TaskTimelineServiceInterface
{
    private const ASSIGNMENT_KEEP = 'keep';
    private const ASSIGNMENT_SET = 'set';
    private const ASSIGNMENT_CLEAR = 'clear';

    public function __construct(
        private readonly TaskServiceInterface $tasks,
        private readonly TaskTimelineContextMetaBuilderInterface $taskTimelineContextMetaBuilder,
    ) {}

    public function syncSubjectTaskContext(
        string $actorUserId,
        string $subjectType,
        string $subjectId,
        array $data,
        string $assignmentMode = self::ASSIGNMENT_KEEP,
        ?string $assignedToUserId = null,
        ?string $title = null,
        ?string $description = null,
        ?string $type = null,
        ?string $note = null
    ): Task {
        $this->assertValidAssignmentMode($assignmentMode);

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

            if ($assignmentMode === self::ASSIGNMENT_SET && $assignedToUserId !== null && $assignedToUserId !== '') {
                $task = $this->tasks->syncTaskContext(
                    taskId: (string) $task->id,
                    data: $data,
                    assignmentMode: self::ASSIGNMENT_SET,
                    assignedToUserId: $assignedToUserId,
                    title: null,
                    description: null,
                    type: null,
                    mergeData: false
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
        $mergedData = array_replace_recursive($previousData, $data);

        $targetAssignee = $this->resolveTargetAssignee($task, $assignmentMode, $assignedToUserId);
        $changeSet = $this->taskTimelineContextMetaBuilder->build(
            task: $task,
            previousData: $previousData,
            mergedData: $mergedData,
            assignmentMode: $assignmentMode,
            targetAssignee: $targetAssignee,
            title: $title,
            description: $description,
            type: $type,
            subjectType: $subjectType,
            subjectId: $subjectId,
        );

        if (! $changeSet['has_changes']) {
            return $task;
        }

        $updatedTask = $this->tasks->syncTaskContext(
            taskId: (string) $task->id,
            data: $mergedData,
            assignmentMode: $assignmentMode,
            assignedToUserId: $assignedToUserId,
            title: $title,
            description: $description,
            type: $type,
            mergeData: false
        );

        $this->tasks->recordEvent(
            actorUserId: $actorUserId,
            taskId: (string) $updatedTask->id,
            eventType: 'timeline_context_updated',
            note: $note ?? 'Task timeline context updated.',
            meta: $changeSet['meta'],
            fromStatus: (string) $task->status,
            toStatus: (string) $updatedTask->status,
        );

        return $updatedTask;
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
        return $this->syncSubjectTaskContext(
            actorUserId: $actorUserId,
            subjectType: $subjectType,
            subjectId: $subjectId,
            data: $data,
            assignmentMode: $assignedToUserId === null ? self::ASSIGNMENT_KEEP : self::ASSIGNMENT_SET,
            assignedToUserId: $assignedToUserId,
            title: $title,
            description: $description,
            type: $type,
            note: $note,
        );
    }

    private function resolveInitialTitle(string $subjectType, string $subjectId, ?string $title): string
    {
        $trimmed = trim((string) $title);
        if ($trimmed !== '') {
            return $trimmed;
        }

        return sprintf('Workflow task for %s %s', $subjectType, $subjectId);
    }

    private function resolveTargetAssignee(Task $task, string $assignmentMode, ?string $assignedToUserId): ?string
    {
        return match ($assignmentMode) {
            self::ASSIGNMENT_KEEP => $task->assigned_to_user_id,
            self::ASSIGNMENT_SET => $assignedToUserId,
            self::ASSIGNMENT_CLEAR => null,
            default => throw new InvalidArgumentException('Invalid assignment mode.'),
        };
    }

    private function assertValidAssignmentMode(string $assignmentMode): void
    {
        if (! in_array($assignmentMode, [self::ASSIGNMENT_KEEP, self::ASSIGNMENT_SET, self::ASSIGNMENT_CLEAR], true)) {
            throw new InvalidArgumentException('Invalid assignment mode. Allowed values: keep, set, clear.');
        }
    }
}
