<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\AddTaskCommentRequest;
use App\Http\Requests\Tasks\ChangeTaskStatusRequest;
use App\Http\Requests\Tasks\DeleteTaskRequest;
use App\Http\Requests\Tasks\ReassignTaskRequest;
use App\Http\Requests\Tasks\RestoreTaskRequest;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Services\Contracts\TaskServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskActionController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService,
        private readonly TaskRepositoryInterface $tasks,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|Staff')
            ->only(['store', 'changeStatus', 'comment', 'reassign', 'claim']);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $actorUserId = (string) $request->user()->id;

        $task = $this->taskService->createAndAssign(
            actorUserId: $actorUserId,
            assigneeUserId: (string) $request->input('assignee_user_id'),
            title: (string) $request->input('title'),
            description: $request->input('description'),
            type: $request->input('type'),
            subjectType: $request->input('subject_type'),
            subjectId: $request->input('subject_id'),
            data: (array) $request->input('data', [])
        );

        return response()->json([
            'message' => 'Task created and assigned.',
            'task' => $task,
        ]);
    }

    public function changeStatus(ChangeTaskStatusRequest $request, string $id): JsonResponse
    {
        $task = $this->tasks->findOrFail($id);
        $this->authorize('updateStatus', $task);

        $actorUserId = (string) $request->user()->id;

        $task = $this->taskService->changeStatus(
            actorUserId: $actorUserId,
            taskId: $id,
            toStatus: (string) $request->input('status'),
            note: $request->input('note')
        );

        return response()->json([
            'message' => 'Task status updated.',
            'task' => $task,
        ]);
    }

    public function comment(AddTaskCommentRequest $request, string $id): JsonResponse
    {
        $task = $this->tasks->findOrFail($id);
        $this->authorize('comment', $task);

        $actorUserId = (string) $request->user()->id;

        $this->taskService->addComment(
            actorUserId: $actorUserId,
            taskId: $id,
            note: (string) $request->input('note')
        );

        return response()->json([
            'message' => 'Comment added.',
        ]);
    }

    public function reassign(ReassignTaskRequest $request, string $id): JsonResponse
    {
        $task = $this->tasks->findOrFail($id);
        $this->authorize('reassign', $task);

        $actorUserId = (string) $request->user()->id;

        $task = $this->taskService->reassign(
            actorUserId: $actorUserId,
            taskId: $id,
            newAssigneeUserId: (string) $request->input('assignee_user_id'),
            note: $request->input('note')
        );

        return response()->json([
            'message' => 'Task reassigned.',
            'task' => $task,
        ]);
    }

    public function claim(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $task = $this->tasks->findOrFail($id);
        $this->authorize('claim', $task);

        $userId = (string) $request->user()->id;

        $task = $this->taskService->claim(
            actorUserId: $userId,
            taskId: $id,
            note: 'Task claimed via UI.'
        );

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Task claimed successfully.',
                'task' => [
                    'id' => (string) $task->id,
                ],
            ]);
        }

        return redirect()
            ->route('tasks.show', $id)
            ->with('success', 'Task claimed successfully.');
    }

    public function destroy(DeleteTaskRequest $request, string $id): JsonResponse
    {
        $task = $this->tasks->findOrFail($id);
        $this->authorize('delete', $task);

        $this->taskService->archive((string) $request->user()->id, $id);

        return response()->json(['message' => 'Task archived successfully.']);
    }

    public function restore(RestoreTaskRequest $request, string $id): JsonResponse
    {
        $task = $this->tasks->findOrFailWithTrashed($id);
        $this->authorize('restore', $task);

        $ok = $this->taskService->restore((string) $request->user()->id, $id);

        if (! $ok) {
            return response()->json(['message' => 'Task could not be restored.'], 422);
        }

        return response()->json(['message' => 'Task restored successfully.']);
    }
}
