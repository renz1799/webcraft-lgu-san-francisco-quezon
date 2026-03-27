<?php

namespace App\Modules\GSO\Http\Controllers\PAR;

use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\PAR\CancelParRequest;
use App\Modules\GSO\Http\Requests\PAR\FinalizeParRequest;
use App\Modules\GSO\Http\Requests\PAR\ReopenParRequest;
use App\Modules\GSO\Http\Requests\PAR\SubmitParRequest;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Services\Contracts\PAR\ParWorkflowServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ParWorkflowController extends Controller
{
    public function __construct(
        private readonly ParWorkflowServiceInterface $workflow,
    ) {
    }

    public function submit(SubmitParRequest $request, Par $par): RedirectResponse
    {
        $this->workflow->submit((string) $request->user()?->id, (string) $par->id);

        return redirect()
            ->route('gso.pars.show', ['par' => (string) $par->id])
            ->with('success', 'PAR submitted.');
    }

    public function reopen(ReopenParRequest $request, Par $par): JsonResponse
    {
        $updated = $this->workflow->reopen((string) $request->user()?->id, (string) $par->id);

        return response()->json([
            'ok' => true,
            'message' => 'PAR reopened to draft.',
            'data' => [
                'par_id' => (string) $updated->id,
                'status' => (string) $updated->status,
            ],
        ]);
    }

    public function finalize(FinalizeParRequest $request, Par $par): RedirectResponse|JsonResponse
    {
        try {
            $this->workflow->finalize((string) $request->user()?->id, (string) $par->id);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'message' => 'PAR finalized. Issuance events generated.',
                    'redirect' => route('gso.pars.show', ['par' => (string) $par->id]),
                ]);
            }

            return redirect()
                ->route('gso.pars.show', ['par' => (string) $par->id])
                ->with('success', 'PAR finalized. Issuance events generated.');
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            $message = 'Cannot finalize PAR. Please review the issues.';
            if (! empty($errors['fund_cluster_mismatch'])) {
                $message = 'Fund cluster mismatch detected. All items in this PAR must belong to the same Fund Cluster.';
            } elseif (! empty($errors['not_in_pool'])) {
                $message = 'One or more items are no longer in the GSO pool.';
            } elseif (! empty($errors)) {
                $first = collect($errors)->flatten()->first();
                if ($first !== null && $first !== '') {
                    $message = (string) $first;
                }
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                    'errors' => $errors,
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors($errors)
                ->withInput();
        }
    }

    public function cancel(CancelParRequest $request, Par $par): RedirectResponse
    {
        $reason = $request->validated('reason');
        $this->workflow->cancel((string) $request->user()?->id, (string) $par->id, $reason);

        return redirect()
            ->route('gso.pars.show', ['par' => (string) $par->id])
            ->with('success', 'PAR cancelled.');
    }
}
