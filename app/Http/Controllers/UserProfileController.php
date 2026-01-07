<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Services\Contracts\UserProfileServiceInterface;
use Illuminate\Contracts\View\View;

class UserProfileController extends Controller
{
    public function __construct(
        private readonly UserProfileServiceInterface $svc
    ) {}

    public function index(): View
    {
        $data = $this->svc->getMailSettingsData(auth()->user());
        return view('pages.profile.mail-settings', $data);
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
