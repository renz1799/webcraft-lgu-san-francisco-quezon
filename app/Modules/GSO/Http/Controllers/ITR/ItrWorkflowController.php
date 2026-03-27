<?php

namespace App\Modules\GSO\Http\Controllers\ITR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\ITR\CancelItrRequest;
use App\Modules\GSO\Http\Requests\ITR\FinalizeItrRequest;
use App\Modules\GSO\Http\Requests\ITR\ReopenItrRequest;
use App\Modules\GSO\Http\Requests\ITR\SubmitItrRequest;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Services\Contracts\ITR\ItrWorkflowServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ItrWorkflowController extends Controller
{
    public function __construct(
        private readonly ItrWorkflowServiceInterface $workflow,
    ) {}

    public function submit(SubmitItrRequest $request, Itr $itr): JsonResponse
    {
        try {
            $updated = $this->workflow->submit((string) $request->user()->id, (string) $itr->id);

            return response()->json([
                'ok' => true,
                'message' => 'ITR submitted.',
                'data' => [
                    'itr_id' => (string) $updated->id,
                    'status' => (string) $updated->status,
                ],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e, 'Cannot submit ITR. Please review the transfer details and selected items.');
        }
    }

    public function reopen(ReopenItrRequest $request, Itr $itr): JsonResponse
    {
        $updated = $this->workflow->reopen((string) $request->user()->id, (string) $itr->id);

        return response()->json([
            'ok' => true,
            'message' => 'ITR reopened to draft.',
            'data' => [
                'itr_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function finalize(FinalizeItrRequest $request, Itr $itr): JsonResponse
    {
        try {
            $updated = $this->workflow->finalize((string) $request->user()->id, (string) $itr->id);

            return response()->json([
                'ok' => true,
                'message' => 'ITR finalized and selected inventory items were transferred.',
                'data' => [
                    'itr_id' => (string) $updated->id,
                    'status' => (string) $updated->status,
                    'itr_number' => (string) ($updated->itr_number ?? ''),
                ],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e, 'Cannot finalize ITR. Please review the current transfer state.');
        }
    }

    public function cancel(CancelItrRequest $request, Itr $itr): JsonResponse
    {
        $updated = $this->workflow->cancel(
            (string) $request->user()->id,
            (string) $itr->id,
            $request->validated('reason')
        );

        return response()->json([
            'ok' => true,
            'message' => 'ITR cancelled.',
            'data' => [
                'itr_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    private function validationError(ValidationException $e, string $defaultMessage): JsonResponse
    {
        $errors = $e->errors();
        $message = $defaultMessage;

        if (!empty($errors['source_state_mismatch'])) {
            $message = 'One or more selected items no longer match the ITR source side.';
        } elseif (!empty($errors['ineligible_items'])) {
            $message = 'One or more selected items are no longer eligible for ITR transfer.';
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




