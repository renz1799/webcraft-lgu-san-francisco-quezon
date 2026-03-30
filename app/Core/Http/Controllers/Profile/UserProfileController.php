<?php

namespace App\Core\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Core\Http\Requests\Profile\UpdateProfileRequest;
use App\Core\Http\Requests\Profile\UpdatePasswordRequest;
use App\Core\Services\Contracts\Access\UserProfileServiceInterface;
use App\Core\Support\ProfileRouteResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class UserProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileServiceInterface $svc,
        private readonly ProfileRouteResolver $profileRoutes,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        if (
            $request->route()?->getName() === 'profile.index'
            && $this->profileRoutes->shouldRedirectGenericProfile($request->user())
        ) {
            return redirect()->to($this->profileRoutes->indexUrl($request->user(), $request->query()));
        }

        $data = $this->svc->getProfileData($request->user());
        $data['profileRoutes'] = $this->profileRoutes->routesFor($request->user());

        return view('profile.index', $data);
    }

    public function update(UpdateProfileRequest $request)
    {
        $result = $this->svc->updateProfile($request->user(), $request->validated());

        return redirect()
            ->to($this->profileRoutes->personalInfoUrl($request->user()))
            ->with('success', $result['message'] ?? 'Profile updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $this->svc->updatePassword($request->user(), $request->validated());

        return redirect()
            ->to($this->profileRoutes->accountSettingsUrl($request->user()))
            ->with('success', 'Password updated successfully.');
    }
}

