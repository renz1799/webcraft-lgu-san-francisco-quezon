<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use Illuminate\Http\Request;
use App\Services\Contracts\TaskServiceInterface;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly TaskServiceInterface $taskService,
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

    public function claim(Request $request, string $id)
    {
        $userId = (string) $request->user()->id;

        $this->taskService->claim(
            actorUserId: $userId,
            taskId: $id,
            note: 'Task claimed via UI.'
        );

        return redirect()
            ->route('tasks.show', $id)
            ->with('success', 'Task claimed successfully.');
    }

}
