<?php

namespace App\Core\Builders\Tasks;

use App\Core\Builders\Tasks\Contracts\TaskDatatableRowBuilderInterface;
use App\Core\Models\Module;
use App\Core\Models\Tasks\Task;
use Illuminate\Support\Facades\Route;

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
            'owner_module_code' => (string) ($task->module?->code ?? ''),
            'owner_module_name' => (string) ($task->module?->name ?? ''),
            'assigned_to_name' => (string) ($task->assigned_to_name ?? '-'),
            'created_at' => $task->created_at?->toDateTimeString(),
            'created_at_text' => $task->created_at?->format('M d, Y h:i A') ?? '-',
            'is_archived' => $isArchived,
            'show_url' => route($this->taskShowRouteName($task), ['id' => (string) $task->id]),
            'claim_url' => $canClaim ? route('tasks.claim', ['id' => (string) $task->id]) : null,
            'archive_url' => ($canArchive && ! $isArchived)
                ? route('tasks.destroy', ['id' => (string) $task->id])
                : null,
            'restore_url' => ($canRestore && $isArchived)
                ? route('tasks.restore', ['id' => (string) $task->id])
                : null,
        ];
    }

    private function taskShowRouteName(Task $task): string
    {
        $moduleCode = strtoupper(trim((string) ($task->module?->code ?? '')));

        if ($moduleCode === '') {
            $moduleId = trim((string) ($task->module_id ?? ''));

            if ($moduleId !== '') {
                $moduleCode = strtoupper((string) (Module::query()->whereKey($moduleId)->value('code') ?? ''));
            }
        }

        if ($moduleCode !== '') {
            $moduleRouteName = strtolower($moduleCode) . '.tasks.show';

            if (Route::has($moduleRouteName)) {
                return $moduleRouteName;
            }
        }

        return 'tasks.show';
    }
}
