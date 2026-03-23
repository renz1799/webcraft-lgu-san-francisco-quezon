<?php

namespace App\Core\Builders\Tasks\Contracts;

use App\Core\Models\Tasks\Task;

interface TaskTimelineContextMetaBuilderInterface
{
    public function build(
        Task $task,
        array $previousData,
        array $mergedData,
        string $assignmentMode,
        ?string $targetAssignee,
        ?string $title,
        ?string $description,
        ?string $type,
        string $subjectType,
        string $subjectId
    ): array;
}
