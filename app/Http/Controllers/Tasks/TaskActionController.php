<?php

namespace App\Http\Controllers\Tasks;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\ChangeTaskStatusRequest;
use App\Http\Requests\Tasks\AddTaskCommentRequest;
use App\Http\Requests\Tasks\ReassignTaskRequest;
use App\Services\Contracts\TaskServiceInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Models\Task;

class TaskActionController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService,
        private readonly TaskRepositoryInterface $tasks,
    ) {
            $this->middleware('role_or_permission:Administrator|Staff')
            ->only(['store', 'changeStatus', 'comment', 'reassign', 'claim']);
    }

    public function store(StoreTaskRequest $request)
    {
        // If StoreTaskRequest::authorize() already calls policy, this is optional.
        // Keeping it here makes it explicit and consistent.
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

    public function changeStatus(ChangeTaskStatusRequest $request, string $id)
    {
        $task = $this->tasks->findOrFail($id);

        // ✅ Only admin OR assignee can update status
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

    public function comment(AddTaskCommentRequest $request, string $id)
    {
        $task = $this->tasks->findOrFail($id);

        // ✅ Everyone who can view can comment (per your gov flow rule)
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

    public function reassign(ReassignTaskRequest $request, string $id)
    {
        $task = $this->tasks->findOrFail($id);

        // ✅ Admin-only (or capability-based) – policy decides
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

    public function claim(Request $request, string $id)
    {
        $task = $this->tasks->findOrFail($id);

        // ✅ Only pooled + eligible role can claim
        $this->authorize('claim', $task);

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
