<?php

namespace App\Modules\Tasks\Builders\Contracts;

use App\Modules\Tasks\Models\Task;

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
