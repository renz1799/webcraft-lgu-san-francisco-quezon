<?php

namespace App\Core\Http\Controllers\Auth;

use App\Core\Data\Auth\RegisterUserData;
use App\Http\Controllers\Controller;
use App\Core\Http\Requests\Auth\LoginRequest;
use App\Core\Http\Requests\Auth\RegisterRequest;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Services\Contracts\Auth\AuthServiceInterface;
use App\Core\Services\Contracts\Auth\RegisterUserServiceInterface;
use App\Core\Services\Contracts\Auth\RegistrationOptionsServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $auth,
        private readonly ModuleAccessServiceInterface $moduleAccess,
        private readonly RegisterUserServiceInterface $registerUser,
        private readonly RegistrationOptionsServiceInterface $registrationOptions,
    ) {}

    public function showSignUpForm(): View
    {
        return view('auth.sign-up', [
            'roles' => $this->registrationOptions->roles(),
        ]);
    }

    public function registrationOptions(): JsonResponse
    {
        return response()->json([
            'roles' => $this->registrationOptions->roleOptions(),
        ]);
    }

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function register(RegisterRequest $request): RedirectResponse|JsonResponse
    {
        $user = $this->registerUser->register(
            actor: $request->user(),
            data: RegisterUserData::fromArray($request->validated()),
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Account created successfully.',
                'data' => [
                    'id' => (string) $user->id,
                    'username' => (string) $user->username,
                    'email' => (string) $user->email,
                ],
            ], 201);
        }

        return back()->with('success', 'Account created successfully.');
    }

    public function login(LoginRequest $request)
    {
        $payload = array_merge($request->validated(), [
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->header('User-Agent', ''), 255, ''),
        ]);

        if (! $this->auth->attemptLogin($payload)) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        $user = auth()->user();
        if ($user && (bool) $user->must_change_password) {
            return redirect()->route('profile.index', ['tab' => 'account-settings'])
                ->with('force_password_change', true);
        }

        return redirect()->intended(
            $this->moduleAccess->postLoginRedirectPathForUser($user)
        );
    }

    public function logout()
    {
        $this->auth->logout();

        return redirect()->route('login');
    }
}
