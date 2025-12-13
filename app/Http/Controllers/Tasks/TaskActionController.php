<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\StoreTaskRequest;
use App\Http\Requests\Tasks\ChangeTaskStatusRequest;
use App\Http\Requests\Tasks\AddTaskCommentRequest;
use App\Http\Requests\Tasks\ReassignTaskRequest;
use App\Services\Contracts\TaskServiceInterface;

class TaskActionController extends Controller
{
    public function __construct(
        private readonly TaskServiceInterface $taskService
    ) {}

    public function store(StoreTaskRequest $request)
    {
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
        $actorUserId = (string) $request->user()->id;

        $task = $this->taskService->claim(
            actorUserId: $actorUserId,
            taskId: $id,
            note: $request->input('note')
        );

        return redirect()->route('tasks.show', $task->id)
            ->with('success', 'Task claimed.');
    }

}
