<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\TaskTableDataRequest;
use App\Models\User;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\Contracts\TaskServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly TaskServiceInterface $taskService,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|Staff')
            ->only(['index', 'data']);
    }

    public function index(): View
    {
        return view('tasks.index');
    }

    public function data(TaskTableDataRequest $request): JsonResponse
    {
        $user = $request->user();

        $params = $request->validated();
        $params['actor_user_id'] = (string) $user->id;
        $params['actor_roles'] = $user->getRoleNames()->values()->all();
        $params['can_view_all'] = $user->hasAnyRole(['Administrator', 'admin'])
            || $user->can('view All Tasks');
        $params['can_archive'] = $user->hasAnyRole(['Administrator', 'admin']);

        $payload = $this->taskService->datatable($params);

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function show(Request $request, string $id): View
    {
        $task = $this->tasks->findOrFail($id);
        $this->authorize('view', $task);

        $events = $this->taskEvents->getForTask($id);
        $subjectUrl = data_get($task->data, 'subject_url');

        $canReassign = $request->user()?->hasAnyRole(['Administrator', 'admin'])
            || $request->user()?->can('modify Reassign Tasks');

        $assignees = [];

        if ($canReassign) {
            $assignees = User::query()
                ->with(['profile'])
                ->where('is_active', true)
                ->orderBy('username')
                ->get(['id', 'username'])
                ->map(function (User $u) {
                    $name = $u->profile?->full_name ?: ($u->username ?: 'Unknown User');

                    return [
                        'id' => (string) $u->id,
                        'name' => trim((string) $name),
                    ];
                })
                ->values()
                ->all();
        }

        return view('tasks.show', compact('task', 'events', 'subjectUrl', 'assignees'));
    }
}
