<?php

namespace App\Core\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CorePasswordResetNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $platformName = $this->platformName();
        $expiryMinutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 30);
        $displayName = trim((string) ($notifiable->profile?->full_name ?: $notifiable->username ?: $notifiable->email ?: ''));

        return (new MailMessage)
            ->subject($platformName . ' Password Reset Request')
            ->greeting($displayName !== '' ? 'Hello ' . $displayName . ',' : 'Hello,')
            ->line('A password reset request was received for your LGU Management System platform account.')
            ->line('This is an automated system message from ' . $platformName . '.')
            ->action('Reset Platform Password', $this->resetUrl($notifiable))
            ->line('This password reset link will expire in ' . $expiryMinutes . ' minutes.')
            ->line('If you did not request this password reset, please report it to your system administrator immediately.')
            ->line('Do not reply to this email.')
            ->salutation($platformName);
    }

    private function platformName(): string
    {
        $name = trim((string) (config('mail.from.name') ?: config('app.name') ?: 'Webcraft LGU Platform'));

        return $name !== '' ? $name : 'Webcraft LGU Platform';
    }
}
