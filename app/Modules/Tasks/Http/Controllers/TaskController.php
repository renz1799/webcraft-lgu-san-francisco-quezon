<?php

namespace App\Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Tasks\Http\Requests\TaskTableDataRequest;
use App\Modules\Tasks\Services\Contracts\TaskReadServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskReadServiceInterface $taskReadService,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|Staff')
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
        $task = $this->taskReadService->findOrFail($id);
        $this->authorize('view', $task);

        return view('tasks::show', $this->taskReadService->showData($request->user(), $task));
    }
}
