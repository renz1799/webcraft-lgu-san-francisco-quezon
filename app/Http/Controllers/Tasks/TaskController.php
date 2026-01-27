<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
        private readonly TaskEventRepositoryInterface $taskEvents,
    ) {
        $this->middleware('role_or_permission:Administrator|view Tasks')
            ->only(['index', 'data']);
    }

    public function index()
    {
        return view('tasks.index');
    }

public function data(Request $request)
{
    try {
        $user = $request->user();
        $userId = (string) $user->id;
        $roles  = $user->getRoleNames()->all(); // role names

        $scope = trim((string) $request->query('scope', 'mine')); // mine | available | all

        // ✅ Admin-only "All Tasks"
        $canViewAll = $user->hasRole('Administrator') || $user->can('view All Tasks');
        if ($scope === 'all' && !$canViewAll) {
            return response()->json([
                'ok' => false,
                'message' => 'You are not allowed to view all tasks.',
            ], 403);
        }

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'scope' => $scope,
            'status' => trim((string) $request->query('status', '')), // '' = all
        ];

        $page = max(1, (int) $request->query('page', 1));
        $size = min(100, max(1, (int) $request->query('size', 15)));

        $paginator = $this->tasks->paginateForTable(
            userId: $userId,
            roles: $roles,
            filters: $filters,
            page: $page,
            size: $size
        );

        return response()->json([
            'ok' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ]);
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
