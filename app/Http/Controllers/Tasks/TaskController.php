<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\TaskTableDataRequest;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\Contracts\TaskServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly TaskEventRepositoryInterface $taskEvents,
        private readonly TaskServiceInterface $taskService,
    ) {
        $this->middleware('role_or_permission:Administrator|Staff')
            ->only(['index', 'data']);
    }

    public function index()
    {
        return view('tasks.index');
    }

    public function data(TaskTableDataRequest $request)
    {
        try {
            $user = $request->user();
            $userId = (string) $user->id;
            $roles = $user->getRoleNames()->all();

            return response()->json(
                $this->taskService->tableData(
                    actorUserId: $userId,
                    roles: $roles,
                    filters: $request->filters(),
                    page: $request->page(),
                    size: $request->size(),
                )
            );
        } catch (\Throwable $e) {
            Log::error('[Tasks.data] Failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to load tasks.',
                'debug' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function show(Request $request, string $id)
    {
        $task = $this->tasks->findOrFail($id);

        $this->authorize('view', $task);

        $events = $this->taskEvents->getForTask($id);
        $subjectUrl = data_get($task->data, 'subject_url');

        return view('tasks.show', compact('task', 'events', 'subjectUrl'));
    }
}
