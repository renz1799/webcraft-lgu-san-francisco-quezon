<?php

namespace App\Core\Http\Controllers\Auth;

use App\Core\Http\Requests\Auth\ResetPasswordRequest;
use App\Core\Models\User;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function __construct(
        private readonly AuditLogServiceInterface $audit,
    ) {}

    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $resetUser = null;

        $status = Password::broker()->reset(
            $payload,
            function (User $user) use ($payload, &$resetUser): void {
                $user->forceFill([
                    'password' => Hash::make((string) $payload['password']),
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                ])->save();

                event(new PasswordReset($user));

                $resetUser = $user->fresh();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->safe()->except('password', 'password_confirmation', 'token'))
                ->withErrors([
                    'email' => __($status),
                ]);
        }

        if ($resetUser instanceof User) {
            $this->audit->record(
                'auth.password_reset.completed',
                $resetUser,
                ['must_change_password' => true],
                ['must_change_password' => false, 'password_changed' => true],
                [
                    'channel' => 'email',
                    'broker' => config('auth.defaults.passwords'),
                    'reset_flow' => 'self_service_email_link',
                ],
                'Password reset completed via emailed reset link.',
                [
                    'summary' => 'Password reset completed for ' . ($resetUser->email ?: 'user'),
                    'subject_label' => $resetUser->email ?: 'User',
                    'sections' => [
                        [
                            'title' => 'Password Reset',
                            'items' => [
                                [
                                    'label' => 'Status',
                                    'value' => 'Password updated successfully via email reset link.',
                                ],
                                [
                                    'label' => 'Reset Flow',
                                    'value' => 'Self-service email reset',
                                ],
                            ],
                        ],
                    ],
                    'request_details' => [
                        'Email' => $resetUser->email ?: 'None',
                        'Username' => $resetUser->username ?: 'None',
                    ],
                ]
            );
        }

        return redirect()->route('login')->with('success', 'Your password has been reset. You can sign in now.');
    }
}
