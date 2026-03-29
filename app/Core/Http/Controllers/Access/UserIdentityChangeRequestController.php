<?php

namespace App\Core\Http\Controllers\Access;

use App\Core\Http\Requests\Profile\ApproveUserIdentityChangeRequest;
use App\Core\Http\Requests\Profile\RejectUserIdentityChangeRequest;
use App\Core\Services\Contracts\Access\UserIdentityChangeRequestServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserIdentityChangeRequestController extends Controller
{
    public function __construct(
        private readonly UserIdentityChangeRequestServiceInterface $service,
    ) {}

    public function index(Request $request): View
    {
        return view('access.identity-change-requests.index', $this->service->indexData(
            reviewer: $request->user(),
            filters: $request->only(['status', 'search']),
            perPage: 15,
        ));
    }

    public function show(Request $request, string $identityChangeRequest): View
    {
        return view('access.identity-change-requests.show', $this->service->showData(
            reviewer: $request->user(),
            requestId: $identityChangeRequest,
        ));
    }

    public function approve(
        ApproveUserIdentityChangeRequest $request,
        string $identityChangeRequest
    ): RedirectResponse {
        $this->service->approve(
            requestId: $identityChangeRequest,
            reviewer: $request->user(),
            reviewNotes: $request->validated('review_notes'),
        );

        return redirect()
            ->route('identity-change-requests.show', $identityChangeRequest)
            ->with('success', 'Identity change request approved successfully.');
    }

    public function reject(
        RejectUserIdentityChangeRequest $request,
        string $identityChangeRequest
    ): RedirectResponse {
        $this->service->reject(
            requestId: $identityChangeRequest,
            reviewer: $request->user(),
            reviewNotes: $request->validated('review_notes'),
        );

        return redirect()
            ->route('identity-change-requests.show', $identityChangeRequest)
            ->with('success', 'Identity change request rejected successfully.');
    }
}
