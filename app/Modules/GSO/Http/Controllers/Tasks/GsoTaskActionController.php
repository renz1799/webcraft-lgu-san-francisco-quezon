<?php

namespace App\Modules\GSO\Http\Controllers\Tasks;

use App\Core\Http\Requests\Tasks\AddTaskCommentRequest;
use App\Core\Http\Requests\Tasks\ChangeTaskStatusRequest;
use App\Core\Http\Requests\Tasks\DeleteTaskRequest;
use App\Core\Http\Requests\Tasks\ReassignTaskRequest;
use App\Core\Http\Requests\Tasks\RestoreTaskRequest;
use App\Core\Models\Module;
use App\Core\Models\Tasks\Task;
use App\Core\Services\Tasks\Contracts\TaskReadServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskServiceInterface;
use App\Core\Support\CurrentContext;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GsoTaskActionController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService,
        private readonly TaskReadServiceInterface $taskReadService,
        private readonly CurrentContext $context,
    ) {
        $this->middleware('permission:tasks.update_status|tasks.comment|tasks.claim|tasks.view_all')
            ->only(['changeStatus', 'comment', 'claim']);

        $this->middleware('permission:tasks.reassign')
            ->only(['reassign']);
    }

    public function changeStatus(ChangeTaskStatusRequest $request, string $id): JsonResponse
    {
        $task = $this->findTaskForCurrentModule($request->user(), $id);
        $this->authorize('updateStatus', $task);

        $updatedTask = $this->taskService->changeStatus(
            actorUserId: (string) $request->user()->id,
            taskId: $id,
            toStatus: (string) $request->input('status'),
            note: $request->input('note')
        );

        return response()->json([
            'message' => 'Task status updated.',
            'task' => $updatedTask,
        ]);
    }

    public function comment(AddTaskCommentRequest $request, string $id): JsonResponse
    {
        $task = $this->findTaskForCurrentModule($request->user(), $id);
        $this->authorize('comment', $task);

        $this->taskService->addComment(
            actorUserId: (string) $request->user()->id,
            taskId: $id,
            note: (string) $request->input('note')
        );

        return response()->json([
            'message' => 'Comment added.',
        ]);
    }

    public function reassign(ReassignTaskRequest $request, string $id): JsonResponse
    {
        $task = $this->findTaskForCurrentModule($request->user(), $id);
        $this->authorize('reassign', $task);

        $updatedTask = $this->taskService->reassign(
            actorUserId: (string) $request->user()->id,
            taskId: $id,
            newAssigneeUserId: (string) $request->input('assignee_user_id'),
            note: $request->input('note')
        );

        return response()->json([
            'message' => 'Task reassigned.',
            'task' => $updatedTask,
        ]);
    }

    public function claim(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $task = $this->findTaskForCurrentModule($request->user(), $id);
        $this->authorize('claim', $task);

        $claimedTask = $this->taskService->claim(
            actorUserId: (string) $request->user()->id,
            taskId: $id,
            note: 'Task claimed via GSO UI.'
        );

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Task claimed successfully.',
                'task' => [
                    'id' => (string) $claimedTask->id,
                ],
            ]);
        }

        return redirect()
            ->route('gso.tasks.show', $id)
            ->with('success', 'Task claimed successfully.');
    }

    public function destroy(DeleteTaskRequest $request, string $id): JsonResponse
    {
        $task = $this->findTaskForCurrentModule($request->user(), $id);
        $this->authorize('delete', $task);

        $this->taskService->archive((string) $request->user()->id, $id);

        return response()->json(['message' => 'Task archived successfully.']);
    }

    public function restore(RestoreTaskRequest $request, string $id): JsonResponse
    {
        $task = $this->findTaskForCurrentModule($request->user(), $id, withTrashed: true);
        $this->authorize('restore', $task);

        $ok = $this->taskService->restore((string) $request->user()->id, $id);

        if (! $ok) {
            return response()->json(['message' => 'Task could not be restored.'], 422);
        }

        return response()->json(['message' => 'Task restored successfully.']);
    }

    private function findTaskForCurrentModule($user, string $taskId, bool $withTrashed = false): Task
    {
        $task = $withTrashed
            ? $this->taskReadService->findAccessibleWithTrashedOrFail($user, $taskId)
            : $this->taskReadService->findAccessibleOrFail($user, $taskId);

        if (trim((string) $task->module_id) === $this->currentModuleId()) {
            return $task;
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
