<?php

namespace App\Core\Http\Controllers\Access;

use App\Core\Data\Users\ModuleUserOnboardingData;
use App\Core\Http\Requests\Users\StoreModuleStaffRequest;
use App\Core\Services\Contracts\Access\ModuleUserOnboardingServiceInterface;
use App\Core\Support\AdminRouteResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ModuleUserOnboardingController extends Controller
{
    public function __construct(
        private readonly ModuleUserOnboardingServiceInterface $onboarding,
    ) {}

    public function create(): View
    {
        return view('access.users.create', $this->onboarding->getCreateData());
    }

    public function store(StoreModuleStaffRequest $request): RedirectResponse
    {
        $result = $this->onboarding->onboard(ModuleUserOnboardingData::fromArray($request->validated()));
        $adminRoutes = app(AdminRouteResolver::class);
        $flashKey = ($result['status'] ?? 'success') === 'noop' ? 'info' : 'success';

        return redirect($adminRoutes->route('access.users.index'))
            ->with($flashKey, (string) ($result['message'] ?? 'Staff onboarding completed.'));
    }
}
