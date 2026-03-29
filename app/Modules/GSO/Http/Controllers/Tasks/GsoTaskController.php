<?php

namespace App\Modules\GSO\Http\Controllers\Tasks;

use App\Core\Http\Requests\Tasks\TaskTableDataRequest;
use App\Core\Models\Module;
use App\Core\Models\Tasks\Task;
use App\Core\Services\Tasks\Contracts\TaskReadServiceInterface;
use App\Core\Support\CurrentContext;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GsoTaskController extends Controller
{
    public function __construct(
        private readonly TaskReadServiceInterface $taskReadService,
        private readonly CurrentContext $context,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|Staff')
            ->only(['index', 'data', 'show']);
    }

    public function index(Request $request): View
    {
        $moduleId = $this->currentModuleId();

        return view('tasks::index', array_merge(
            $this->taskReadService->indexData($request->user(), $moduleId),
            $this->indexViewOverrides($moduleId)
        ));
    }

    public function data(TaskTableDataRequest $request): JsonResponse
    {
        $payload = $this->taskReadService->datatable(
            $request->user(),
            $request->validated(),
            $this->currentModuleId()
        );

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function show(Request $request, string $id): View
    {
        $task = $this->taskReadService->findAccessibleOrFail($request->user(), $id);
        $this->ensureTaskBelongsToCurrentModule($task);
        $this->authorize('view', $task);

        return view('tasks::show', array_merge(
            $this->taskReadService->showData($request->user(), $task),
            $this->showViewOverrides()
        ));
    }

    private function indexViewOverrides(string $moduleId): array
    {
        return [
            'taskRouteNames' => $this->routeNames(),
            'tasksPageTitle' => 'GSO Tasks',
            'tasksPageDescription' => 'Track assigned work, claimable tasks, and archived records for General Services Office workflows.',
            'tasksBreadcrumbRootLabel' => 'General Services Office',
            'tasksBreadcrumbRootUrl' => route('gso.dashboard'),
            'tasksBreadcrumbLabel' => 'Tasks',
            'tasksQueueTitle' => 'GSO Task Queue',
            'tasksQueueDescription' => 'This queue shows only tasks owned by General Services Office workflows while reusing the shared task engine.',
            'tasksLockedModuleId' => $moduleId,
            'tasksShowModuleFilter' => false,
        ];
    }

    private function showViewOverrides(): array
    {
        return [
            'taskRouteNames' => $this->routeNames(),
            'tasksShowPageDescription' => 'Review task details, take action, and follow the complete GSO workflow timeline from one page.',
            'tasksShowBreadcrumbRootLabel' => 'General Services Office',
            'tasksShowBreadcrumbRootUrl' => route('gso.dashboard'),
            'tasksShowBreadcrumbIndexLabel' => 'Tasks',
            'tasksShowBreadcrumbCurrentLabel' => 'Timeline',
            'tasksShowOverviewDescription' => 'Summary, ownership, and available task actions stay on the left.',
        ];
    }

    private function routeNames(): array
    {
        return [
            'index' => 'gso.tasks.index',
            'data' => 'gso.tasks.data',
            'show' => 'gso.tasks.show',
            'claim' => 'gso.tasks.claim',
            'destroy' => 'gso.tasks.destroy',
            'restore' => 'gso.tasks.restore',
            'status.update' => 'gso.tasks.status.update',
            'reassign' => 'gso.tasks.reassign',
            'comment.store' => 'gso.tasks.comment.store',
        ];
    }

    private function ensureTaskBelongsToCurrentModule(Task $task): void
    {
        if (trim((string) $task->module_id) === $this->currentModuleId()) {
            return;
        }

        $exception = new ModelNotFoundException();
        $exception->setModel(Task::class);

        throw $exception;
    }

    private function currentModuleId(): string
    {
        $moduleId = trim((string) ($this->context->moduleId() ?? ''));

        if ($moduleId !== '') {
            return $moduleId;
        }

        $fallback = (string) (Module::query()
            ->where('code', 'GSO')
            ->where('is_active', true)
            ->value('id') ?? '');

        if ($fallback !== '') {
            return $fallback;
        }

        abort(404);
    }
}
