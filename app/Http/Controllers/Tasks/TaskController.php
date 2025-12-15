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

        // IMPORTANT: use role NAMES (strings), not Role models
        $roles = $user->getRoleNames()->all(); // e.g. ['staff'] or ['admin']

        $availableTasks = $this->tasks->getAvailableForRoles($roles, 20);

        return view('tasks.index', compact('myTasks', 'availableTasks'));
    }

    public function show(Request $request, string $id)
    {
        $task = $this->tasks->findOrFail($id);

        // ✅ policy hook (view)
        $this->authorize('view', $task);

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
