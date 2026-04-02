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
            function (User $user) use ($payload, $isInvitationFlow, &$resetUser): void {
                $updates = [
                    'password' => Hash::make((string) $payload['password']),
                    'remember_token' => Str::random(60),
                    'must_change_password' => false,
                ];

                if ($isInvitationFlow && $user->email_verified_at === null) {
                    $updates['email_verified_at'] = now();
                }

                $user->forceFill($updates)->save();

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
            $changesOld = [
                'must_change_password' => true,
            ];

            $changesNew = [
                'must_change_password' => false,
                'password_changed' => true,
            ];

            if ($isInvitationFlow && $resetUser->email_verified_at !== null) {
                $changesNew['email_verified_at'] = $resetUser->email_verified_at->toDateTimeString();
            }

            $this->audit->record(
                $isInvitationFlow ? 'auth.invitation.completed' : 'auth.password_reset.completed',
                $resetUser,
                $changesOld,
                $changesNew,
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
                                [
                                    'label' => 'Email Verification',
                                    'value' => $isInvitationFlow
                                        ? ($resetUser->email_verified_at ? 'Verified from invitation link' : 'Pending')
                                        : ($resetUser->email_verified_at ? 'Already verified' : 'Unchanged'),
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
