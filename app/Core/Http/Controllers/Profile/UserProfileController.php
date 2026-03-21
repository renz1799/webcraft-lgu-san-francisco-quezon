<?php

namespace App\Core\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Core\Http\Requests\Profile\UpdateProfileRequest;
use App\Core\Http\Requests\Profile\UpdatePasswordRequest;
use App\Core\Services\Contracts\Access\UserProfileServiceInterface;
use Illuminate\Contracts\View\View;

class UserProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileServiceInterface $svc
    ) {}

    public function index(): View
    {
        $data = $this->svc->getProfileData(auth()->user());
        return view('profile.index', $data);
    }

    public function update(UpdateProfileRequest $request)
    {
        $this->svc->updateProfile($request->user(), $request->validated());

        return redirect()
            ->route('profile.index')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $this->svc->updatePassword($request->user(), $request->validated());

        return redirect()
            ->route('profile.index', ['tab' => 'account-settings'])
            ->with('success', 'Password updated successfully.');
    }
}



