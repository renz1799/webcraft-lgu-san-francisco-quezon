<?php

namespace App\Modules\Tasks\Services\Contracts;

use App\Modules\Tasks\Models\Task;

interface TaskTimelineServiceInterface
{
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
