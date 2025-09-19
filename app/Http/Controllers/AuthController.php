<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Contracts\AuthServiceInterface;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function __construct(private readonly AuthServiceInterface $auth) {}

    public function showSignUpForm()
    {
        $roles = Role::all(); // Spatie Role
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
            'user_agent' => $request->header('User-Agent', ''),
        ]);

        if (! $this->auth->attemptLogin($payload)) {
            return back()->withErrors(['email' => 'The provided credentials do not match our records.'])
                         ->onlyInput('email');
        }

        return redirect()->intended('/');
    }

    public function logout()
    {
        $this->auth->logout();
        return redirect('/login');
    }
}
