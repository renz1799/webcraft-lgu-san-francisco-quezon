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
        $flow = $request->query('flow') === 'invitation' ? 'invitation' : 'reset';

        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
            'flow' => $flow,
        ]);
    }

    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $resetUser = null;
        $flow = $request->input('flow') === 'invitation' ? 'invitation' : 'reset';
        $isInvitationFlow = $flow === 'invitation';

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
                $isInvitationFlow ? 'auth.invitation.completed' : 'auth.password_reset.completed',
                $resetUser,
                ['must_change_password' => true],
                ['must_change_password' => false, 'password_changed' => true],
                [
                    'channel' => 'email',
                    'broker' => config('auth.defaults.passwords'),
                    'reset_flow' => $isInvitationFlow ? 'invitation_password_setup' : 'self_service_email_link',
                ],
                $isInvitationFlow
                    ? 'Account invitation completed by setting a password from the emailed invitation.'
                    : 'Password reset completed via emailed reset link.',
                [
                    'summary' => ($isInvitationFlow ? 'Invitation completed for ' : 'Password reset completed for ') . ($resetUser->email ?: 'user'),
                    'subject_label' => $resetUser->email ?: 'User',
                    'sections' => [
                        [
                            'title' => $isInvitationFlow ? 'Account Setup' : 'Password Reset',
                            'items' => [
                                [
                                    'label' => 'Status',
                                    'value' => $isInvitationFlow
                                        ? 'Password created successfully from the invitation email.'
                                        : 'Password updated successfully via email reset link.',
                                ],
                                [
                                    'label' => 'Flow',
                                    'value' => $isInvitationFlow ? 'Invitation password setup' : 'Self-service email reset',
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

        return redirect()->route('login')->with(
            'success',
            $isInvitationFlow
                ? 'Your password has been set. You can sign in now.'
                : 'Your password has been reset. You can sign in now.'
        );
    }
}
