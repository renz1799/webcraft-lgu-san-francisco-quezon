<?php

namespace App\Builders\Tasks;

use App\Builders\Contracts\Tasks\TaskTimelineContextMetaBuilderInterface;
use App\Models\Task;
use Illuminate\Support\Arr;

class TaskTimelineContextMetaBuilder implements TaskTimelineContextMetaBuilderInterface
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
    ): array {
        $changedDataKeys = $this->diffDataKeys($previousData, $mergedData);
        $assigneeChanged = (string) ($task->assigned_to_user_id ?? '') !== (string) ($targetAssignee ?? '');
        $titleChanged = $title !== null && $task->title !== $title;
        $descriptionChanged = $description !== null && $task->description !== $description;
        $typeChanged = $type !== null && $task->type !== $type;

        return [
            'has_changes' => $changedDataKeys !== [] || $assigneeChanged || $titleChanged || $descriptionChanged || $typeChanged,
            'meta' => [
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'assignment_mode' => $assignmentMode,
                'changed_data_keys' => $changedDataKeys,
                'assignee_changed' => $assigneeChanged,
                'from_assigned_to_user_id' => $task->assigned_to_user_id,
                'to_assigned_to_user_id' => $targetAssignee,
                'title_changed' => $titleChanged,
                'description_changed' => $descriptionChanged,
                'type_changed' => $typeChanged,
            ],
        ];
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
