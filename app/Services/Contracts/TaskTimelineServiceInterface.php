<?php

namespace App\Services\Contracts;

use App\Models\Task;

interface TaskTimelineServiceInterface
{
    public function findLatestBySubject(string $subjectType, string $subjectId): ?Task;

    public function createUnassigned(
        string $actorUserId,
        string $title,
        ?string $description = null,
        ?string $type = null,
        ?string $subjectType = null,
        ?string $subjectId = null,
        array $data = []
    ): Task;

    public function updateTaskAssignmentAndData(
        string $taskId,
        ?string $assignedToUserId,
        array $data
    ): Task;

    public function recordEvent(
        string $actorUserId,
        string $taskId,
        string $eventType,
        ?string $note = null,
        array $meta = [],
        ?string $fromStatus = null,
        ?string $toStatus = null
    ): void;

    public function syncSubjectTaskContext(
        string $actorUserId,
        string $subjectType,
        string $subjectId,
        array $data,
        string $assignmentMode = 'keep',
        ?string $assignedToUserId = null,
        ?string $title = null,
        ?string $description = null,
        ?string $type = null,
        ?string $note = null
    ): Task;

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
    ): Task;
}