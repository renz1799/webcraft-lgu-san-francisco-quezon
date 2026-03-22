<?php

namespace App\Core\Http\Controllers\Auth;

use App\Core\Http\Requests\Auth\ForgotPasswordLinkRequest;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly AuditLogServiceInterface $audit,
    ) {}

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(ForgotPasswordLinkRequest $request): RedirectResponse
    {
        $email = (string) $request->validated()['email'];
        $user = $this->users->findByEmail($email);

        $status = Password::broker()->sendResetLink([
            'email' => $email,
        ]);

        if ($status === Password::RESET_LINK_SENT && $user) {
            $expiryMinutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 30);

            $this->audit->record(
                'auth.password_reset.link_requested',
                $user,
                [],
                ['email' => $user->email],
                [
                    'channel' => 'email',
                    'broker' => config('auth.defaults.passwords'),
                    'expires_in_minutes' => $expiryMinutes,
                ],
                'Password reset link requested via self-service form.',
                [
                    'summary' => 'Password reset link requested for ' . ($user->email ?: 'user'),
                    'subject_label' => $user->email ?: 'User',
                    'sections' => [
                        [
                            'title' => 'Reset Link Request',
                            'items' => [
                                [
                                    'label' => 'Delivery',
                                    'value' => 'Password reset link sent by email.',
                                ],
                                [
                                    'label' => 'Expires In',
                                    'value' => $expiryMinutes . ' minutes',
                                ],
                            ],
                        ],
                    ],
                    'request_details' => [
                        'Email' => $user->email ?: 'None',
                    ],
                ]
            );
        }

        return back()->with('status', 'If an account with that email exists, a password reset link has been sent.');
    }
}
