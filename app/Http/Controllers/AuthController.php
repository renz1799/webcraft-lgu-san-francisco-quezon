<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function __construct(private readonly AuthServiceInterface $auth) {}

    public function showSignUpForm()
    {
        // Safer: avoid exposing/admin-assigning admin role in UI
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->where('name', '!=', 'admin')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('auth.sign-up', compact('roles'));
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function register(RegisterRequest $request)
    {
        $this->auth->register($request->validated());
        return redirect()->route('login')->with('success', 'Account created successfully. Please log in.');
    }

    public function login(LoginRequest $request)
    {
        $payload = array_merge($request->validated(), [
            'ip'         => $request->ip(),
            'user_agent' => Str::limit((string) $request->header('User-Agent', ''), 255, ''),
        ]);

        if (! $this->auth->attemptLogin($payload)) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        $user = auth()->user();
        if ($user && (bool) $user->must_change_password) {
            return redirect('/mail-settings?tab=account-settings')
                ->with('force_password_change', true);
        }

        return redirect()->intended('/');
    }

    public function logout()
    {
        $this->auth->logout();
        return redirect('/login');
    }
}
