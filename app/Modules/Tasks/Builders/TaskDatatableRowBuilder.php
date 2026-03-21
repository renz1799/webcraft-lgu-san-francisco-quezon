<?php

namespace App\Modules\Tasks\Builders;

use App\Modules\Tasks\Builders\Contracts\TaskDatatableRowBuilderInterface;
use App\Modules\Tasks\Models\Task;

class TaskDatatableRowBuilder implements TaskDatatableRowBuilderInterface
{
    public function build(Task $task, array $context = []): array
    {
        $isArchived = $task->deleted_at !== null;
        $canClaim = (bool) ($context['can_claim'] ?? false);
        $canArchive = (bool) ($context['can_archive'] ?? false);
        $canRestore = (bool) ($context['can_restore'] ?? false);

        return [
            'id' => (string) $task->id,
            'title' => (string) ($task->title ?? '-'),
            'status' => (string) ($task->status ?? '-'),
            'assigned_to_name' => (string) ($task->assigned_to_name ?? '-'),
            'created_at' => $task->created_at?->toDateTimeString(),
            'created_at_text' => $task->created_at?->format('M d, Y h:i A') ?? '-',
            'is_archived' => $isArchived,
            'show_url' => route('tasks.show', ['id' => (string) $task->id]),
            'claim_url' => $canClaim ? route('tasks.claim', ['id' => (string) $task->id]) : null,
            'archive_url' => ($canArchive && ! $isArchived)
                ? route('tasks.destroy', ['id' => (string) $task->id])
                : null,
            'restore_url' => ($canRestore && $isArchived)
                ? route('tasks.restore', ['id' => (string) $task->id])
                : null,
        ];
    }
}
