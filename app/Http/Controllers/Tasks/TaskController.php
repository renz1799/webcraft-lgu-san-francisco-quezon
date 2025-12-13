<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly TaskEventRepositoryInterface $taskEvents,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $userId = (string) $user->id;

        $myTasks = $this->tasks->paginateForAssignee($userId, 20);

        // "Available" pooled tasks: assigned_to_user_id is null and user has eligible role (from data->eligible_roles)
        // For v1, we’ll fetch pooled tasks via query inside repo later.
        // For now, simplest: show none until we add repo method (recommended below).
        $availableTasks = collect(); // placeholder

        return view('tasks.index', compact('myTasks', 'availableTasks'));
    }

    public function show(Request $request, string $id)
    {
        $task = $this->tasks->findOrFail($id);
        $events = $this->taskEvents->getForTask($id);

        $subjectUrl = data_get($task->data, 'subject_url');

        return view('tasks.show', compact('task', 'events', 'subjectUrl'));
    }


    private function resolveSubjectUrl(?string $subjectType, ?string $subjectId): ?string
    {
        if (!$subjectType || !$subjectId) return null;

        // v1: table-name based mapping (extend as needed)
        return match ($subjectType) {
            'inventory_items' => route('inventory.items.show', $subjectId), // adjust to your real route name
            default => null,
        };
    }
}
