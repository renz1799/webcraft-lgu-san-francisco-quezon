<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tasks\AssignTaskRequest;
use App\Services\Tasks\TaskAssignmentService;
use Illuminate\Http\JsonResponse;

class TaskAssignmentController extends Controller
{
    public function __construct(
        private readonly TaskAssignmentService $assignmentService
    ) {
            $this->middleware('role_or_permission:Administrator|Staff')
            ->only(['assign']);
    }

    public function assign(AssignTaskRequest $request): JsonResponse
    {
        $actorUserId = (string) $request->user()->id;

        $this->assignmentService->assign(
            actorUserId: $actorUserId,
            taskId: (string) $request->input('task_id'),
            assigneeUserId: (string) $request->input('assignee_user_id')
        );

        return response()->json([
            'message' => 'Task assigned and notification created.',
        ]);
    }
}
