<?php

namespace App\Core\Notifications\Auth;

use App\Core\Notifications\Concerns\ResolvesPlatformMailBranding;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class UserInvitationNotification extends BaseResetPassword
{
    use ResolvesPlatformMailBranding;

    public function __construct(
        string $token,
        private readonly string $moduleName,
        private readonly ?string $departmentName = null,
        private readonly ?string $roleName = null,
    ) {
        parent::__construct($token);
    }

    public function toMail($notifiable): MailMessage
    {
        $platformName = $this->platformName();
        $expiryMinutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 30);

        $mail = (new MailMessage)
            ->subject("You're invited to {$platformName}")
            ->greeting($this->displayName($notifiable) . ',')
            ->line('A platform account has been created for you in the LGU San Francisco Integrated Information System.')
            ->line('This is an automated system message from ' . $platformName . '.')
            ->line('Your account has been prepared for access to ' . $this->moduleName . '.');

        if ($this->departmentName) {
            $mail->line('Assigned Department: ' . $this->departmentName);
        }

        if ($this->roleName) {
            $mail->line('Assigned Role: ' . $this->roleName);
        }

        return $mail
            ->action('Set Your Password', $this->resetUrl($notifiable))
            ->line('This secure account setup link will expire in ' . $expiryMinutes . ' minutes.')
            ->line('If you were not expecting this invitation, please report it to your system administrator immediately.')
            ->line('Do not reply to this email.')
            ->salutation($platformName);
    }

    protected function resetUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable, $this->token);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
            'flow' => 'invitation',
        ], false));
    }
}
