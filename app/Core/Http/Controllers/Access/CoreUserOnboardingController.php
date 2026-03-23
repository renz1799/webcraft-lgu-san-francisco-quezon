<?php

namespace App\Core\Http\Controllers\Access;

use App\Core\Data\Users\CoreUserOnboardingData;
use App\Core\Http\Requests\Users\StoreCoreUserOnboardingRequest;
use App\Core\Services\Contracts\Access\CoreUserOnboardingServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CoreUserOnboardingController extends Controller
{
    public function __construct(
        private readonly CoreUserOnboardingServiceInterface $onboarding,
    ) {}

    public function create(): View
    {
        return view('access.users.create', $this->onboarding->getCreateData());
    }

    public function store(StoreCoreUserOnboardingRequest $request): RedirectResponse
    {
        $result = $this->onboarding->onboard(
            actor: $request->user(),
            data: CoreUserOnboardingData::fromArray($request->validated()),
        );

        $flashKey = ($result['status'] ?? 'success') === 'noop' ? 'info' : 'success';

        return redirect()->route('access.users.index')
            ->with($flashKey, (string) ($result['message'] ?? 'User onboarding completed.'));
    }
}
