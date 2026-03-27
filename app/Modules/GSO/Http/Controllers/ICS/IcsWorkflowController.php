<?php

namespace App\Modules\GSO\Http\Controllers\ICS;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\ICS\CancelIcsRequest;
use App\Modules\GSO\Http\Requests\ICS\FinalizeIcsRequest;
use App\Modules\GSO\Http\Requests\ICS\ReopenIcsRequest;
use App\Modules\GSO\Http\Requests\ICS\SubmitIcsRequest;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Services\Contracts\ICS\IcsWorkflowServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class IcsWorkflowController extends Controller
{
    public function __construct(
        private readonly IcsWorkflowServiceInterface $workflow,
    ) {}

    public function submit(SubmitIcsRequest $request, Ics $ics): JsonResponse
    {
        $updated = $this->workflow->submit((string) $request->user()?->id, (string) $ics->id);

        return response()->json([
            'ok' => true,
            'message' => 'ICS submitted.',
            'data' => [
                'ics_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function reopen(ReopenIcsRequest $request, Ics $ics): JsonResponse
    {
        $updated = $this->workflow->reopen((string) $request->user()?->id, (string) $ics->id);

        return response()->json([
            'ok' => true,
            'message' => 'ICS reopened to draft.',
            'data' => [
                'ics_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function finalize(FinalizeIcsRequest $request, Ics $ics): JsonResponse
    {
        try {
            $updated = $this->workflow->finalize((string) $request->user()?->id, (string) $ics->id);

            return response()->json([
                'ok' => true,
                'message' => 'ICS finalized. Issuance events generated.',
                'data' => [
                    'ics_id' => (string) $updated->id,
                    'status' => (string) $updated->status,
                ],
                'redirect' => route('gso.ics.edit', ['ics' => (string) $ics->id]),
            ]);
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            $message = 'Cannot finalize ICS. Please review the issues.';
            if (! empty($errors['fund_cluster_mismatch'])) {
                $message = 'Fund cluster mismatch detected. All items in this ICS must belong to the same Fund Cluster.';
            } elseif (! empty($errors['not_in_pool'])) {
                $message = 'One or more items are no longer in the GSO pool.';
            } elseif (! empty($errors)) {
                $first = collect($errors)->flatten()->first();
                if ($first !== null && $first !== '') {
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

    public function cancel(CancelIcsRequest $request, Ics $ics): JsonResponse
    {
        $updated = $this->workflow->cancel(
            (string) $request->user()?->id,
            (string) $ics->id,
            $request->validated('reason'),
        );

        return response()->json([
            'ok' => true,
            'message' => 'ICS cancelled.',
            'data' => [
                'ics_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }
}
