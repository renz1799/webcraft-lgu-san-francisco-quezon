<?php

namespace App\Modules\GSO\Http\Controllers\PTR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\PTR\CancelPtrRequest;
use App\Modules\GSO\Http\Requests\PTR\FinalizePtrRequest;
use App\Modules\GSO\Http\Requests\PTR\ReopenPtrRequest;
use App\Modules\GSO\Http\Requests\PTR\SubmitPtrRequest;
use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Services\Contracts\PTR\PtrWorkflowServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PtrWorkflowController extends Controller
{
    public function __construct(
        private readonly PtrWorkflowServiceInterface $workflow,
    ) {}

    public function submit(SubmitPtrRequest $request, Ptr $ptr): JsonResponse
    {
        try {
            $updated = $this->workflow->submit((string) $request->user()->id, (string) $ptr->id);

            return response()->json([
                'ok' => true,
                'message' => 'PTR submitted.',
                'data' => [
                    'ptr_id' => (string) $updated->id,
                    'status' => (string) $updated->status,
                ],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e, 'Cannot submit PTR. Please review the transfer details and selected items.');
        }
    }

    public function reopen(ReopenPtrRequest $request, Ptr $ptr): JsonResponse
    {
        $updated = $this->workflow->reopen((string) $request->user()->id, (string) $ptr->id);

        return response()->json([
            'ok' => true,
            'message' => 'PTR reopened to draft.',
            'data' => [
                'ptr_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function finalize(FinalizePtrRequest $request, Ptr $ptr): JsonResponse
    {
        try {
            $updated = $this->workflow->finalize((string) $request->user()->id, (string) $ptr->id);

            return response()->json([
                'ok' => true,
                'message' => 'PTR finalized and selected inventory items were transferred.',
                'data' => [
                    'ptr_id' => (string) $updated->id,
                    'status' => (string) $updated->status,
                    'ptr_number' => (string) ($updated->ptr_number ?? ''),
                ],
            ]);
        } catch (ValidationException $e) {
            return $this->validationError($e, 'Cannot finalize PTR. Please review the current transfer state.');
        }
    }

    public function cancel(CancelPtrRequest $request, Ptr $ptr): JsonResponse
    {
        $updated = $this->workflow->cancel(
            (string) $request->user()->id,
            (string) $ptr->id,
            $request->validated('reason')
        );

        return response()->json([
            'ok' => true,
            'message' => 'PTR cancelled.',
            'data' => [
                'ptr_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    private function validationError(ValidationException $e, string $defaultMessage): JsonResponse
    {
        $errors = $e->errors();
        $message = $defaultMessage;

        if (!empty($errors['source_state_mismatch'])) {
            $message = 'One or more selected items no longer match the PTR source side.';
        } elseif (!empty($errors['ineligible_items'])) {
            $message = 'One or more selected items are no longer eligible for PTR transfer.';
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
