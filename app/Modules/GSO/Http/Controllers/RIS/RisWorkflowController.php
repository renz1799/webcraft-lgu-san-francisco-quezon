<?php

namespace App\Modules\GSO\Http\Controllers\RIS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\RIS\Workflow\ApproveRisRequest;
use App\Modules\GSO\Http\Requests\RIS\Workflow\RejectRisRequest;
use App\Modules\GSO\Http\Requests\RIS\Workflow\ReopenRisRequest;
use App\Modules\GSO\Http\Requests\RIS\Workflow\RevertRisRequest;
use App\Modules\GSO\Http\Requests\RIS\Workflow\SubmitRisRequest;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Services\Contracts\RIS\RisWorkflowServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class RisWorkflowController extends Controller
{
    public function __construct(
        private readonly RisWorkflowServiceInterface $workflow,
    ) {
    }

    public function submit(SubmitRisRequest $request, Ris $ris): JsonResponse
    {
        Log::info('[RIS] submit()', [
            'ris_id' => (string) $ris->id,
            'user_id' => (string) $request->user()?->id,
        ]);

        $updated = $this->workflow->submit(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
        );

        return response()->json([
            'message' => 'RIS submitted.',
            'data' => [
                'ris_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function approve(ApproveRisRequest $request, Ris $ris): JsonResponse
    {
        Log::info('[RIS] approve()', [
            'ris_id' => (string) $ris->id,
            'user_id' => (string) $request->user()?->id,
        ]);

        $updated = $this->workflow->approveIssue(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
        );

        return response()->json([
            'message' => 'RIS issued and stocks deducted.',
            'data' => [
                'ris_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function reject(RejectRisRequest $request, Ris $ris): JsonResponse
    {
        $validated = $request->validated();

        Log::info('[RIS] reject()', [
            'ris_id' => (string) $ris->id,
            'user_id' => (string) $request->user()?->id,
        ]);

        $updated = $this->workflow->reject(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
            reason: $validated['reason'] ?? null,
        );

        return response()->json([
            'message' => 'RIS rejected.',
            'data' => [
                'ris_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function reopen(ReopenRisRequest $request, Ris $ris): JsonResponse
    {
        Log::info('[RIS] reopen()', [
            'ris_id' => (string) $ris->id,
            'user_id' => (string) $request->user()?->id,
        ]);

        $updated = $this->workflow->reopen(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
        );

        return response()->json([
            'message' => 'RIS reopened to draft.',
            'data' => [
                'ris_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function revertToDraft(RevertRisRequest $request, Ris $ris): JsonResponse
    {
        Log::info('[RIS] revertToDraft()', [
            'ris_id' => (string) $ris->id,
            'user_id' => (string) $request->user()?->id,
        ]);

        $updated = $this->workflow->revertToDraft(
            actorUserId: (string) $request->user()?->id,
            risId: (string) $ris->id,
        );

        return response()->json([
            'message' => 'RIS reverted to draft; stocks re-added.',
            'data' => [
                'ris_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }
}
