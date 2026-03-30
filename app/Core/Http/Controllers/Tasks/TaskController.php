<?php

namespace App\Core\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Core\Http\Requests\Tasks\TaskTableDataRequest;
use App\Core\Services\Tasks\Contracts\TaskReadServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskReadServiceInterface $taskReadService,
    ) {
        $this->middleware('permission:tasks.view|tasks.view_all|tasks.claim|tasks.comment|tasks.update_status|tasks.reassign')
            ->only(['index', 'data', 'show']);
    }

    public function index(Request $request): View
    {
        return view('tasks::index', $this->taskReadService->indexData($request->user()));
    }

    public function data(TaskTableDataRequest $request): JsonResponse
    {
        $payload = $this->taskReadService->datatable($request->user(), $request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function show(Request $request, string $id): View
    {
        $task = $this->taskReadService->findAccessibleOrFail($request->user(), $id);
        $this->authorize('view', $task);

        return view('tasks::show', $this->taskReadService->showData($request->user(), $task));
    }
}
