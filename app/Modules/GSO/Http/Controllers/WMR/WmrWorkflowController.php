<?php

namespace App\Modules\GSO\Http\Controllers\WMR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\WMR\ApproveWmrRequest;
use App\Modules\GSO\Http\Requests\WMR\CancelWmrRequest;
use App\Modules\GSO\Http\Requests\WMR\FinalizeWmrRequest;
use App\Modules\GSO\Http\Requests\WMR\ReopenWmrRequest;
use App\Modules\GSO\Http\Requests\WMR\SubmitWmrRequest;
use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Services\Contracts\WMR\WmrWorkflowServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class WmrWorkflowController extends Controller
{
    public function __construct(
        private readonly WmrWorkflowServiceInterface $workflow,
    ) {}

    public function submit(SubmitWmrRequest $request, Wmr $wmr): JsonResponse
    {
        try {
            $updated = $this->workflow->submit((string) $request->user()->id, (string) $wmr->id);

            return response()->json([
                'ok' => true,
                'message' => 'WMR submitted.',
                'data' => [
                    'wmr_id' => (string) $updated->id,
                    'status' => (string) $updated->status,
                ],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e, 'Cannot submit WMR. Please review the report details and selected disposal items.');
        }
    }

    public function approve(ApproveWmrRequest $request, Wmr $wmr): JsonResponse
    {
        try {
            $updated = $this->workflow->approve((string) $request->user()->id, (string) $wmr->id);

            return response()->json([
                'ok' => true,
                'message' => 'WMR approved for disposal.',
                'data' => [
                    'wmr_id' => (string) $updated->id,
                    'status' => (string) $updated->status,
                ],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e, 'Cannot approve WMR. Please review the approval details and selected disposal items.');
        }
    }

    public function reopen(ReopenWmrRequest $request, Wmr $wmr): JsonResponse
    {
        $updated = $this->workflow->reopen((string) $request->user()->id, (string) $wmr->id);

        return response()->json([
            'ok' => true,
            'message' => 'WMR reopened to draft.',
            'data' => [
                'wmr_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function finalize(FinalizeWmrRequest $request, Wmr $wmr): JsonResponse
    {
        try {
            $updated = $this->workflow->finalize((string) $request->user()->id, (string) $wmr->id);

            return response()->json([
                'ok' => true,
                'message' => 'WMR finalized and selected items were marked for disposal.',
                'data' => [
                    'wmr_id' => (string) $updated->id,
                    'status' => (string) $updated->status,
                    'wmr_number' => (string) ($updated->wmr_number ?? ''),
                ],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e, 'Cannot finalize WMR. Please review the current disposal state.');
        }
    }

    public function cancel(CancelWmrRequest $request, Wmr $wmr): JsonResponse
    {
        $updated = $this->workflow->cancel(
            (string) $request->user()->id,
            (string) $wmr->id,
            $request->validated('reason')
        );

        return response()->json([
            'ok' => true,
            'message' => 'WMR cancelled.',
            'data' => [
                'wmr_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    private function validationError(ValidationException $e, string $defaultMessage): JsonResponse
    {
        $errors = $e->errors();
        $message = $defaultMessage;

        if (!empty($errors['fund_cluster_mismatch'])) {
            $message = 'One or more selected items no longer match the WMR fund cluster.';
        } elseif (!empty($errors['ineligible_items'])) {
            $message = 'One or more selected items are no longer eligible for disposal.';
        } else {
            $first = collect($errors)->flatten()->first();
            if (!empty($first)) {
                $message = (string) $first;
            }
        }

        return response()->json([
            'ok' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }
}
