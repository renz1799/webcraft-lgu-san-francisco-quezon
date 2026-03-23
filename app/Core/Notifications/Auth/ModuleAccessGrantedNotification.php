<?php

namespace App\Core\Notifications\Auth;

use App\Core\Notifications\Concerns\ResolvesPlatformMailBranding;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ModuleAccessGrantedNotification extends Notification
{
    use ResolvesPlatformMailBranding;

    public function __construct(
        private readonly string $moduleName,
        private readonly ?string $departmentName = null,
        private readonly ?string $roleName = null,
        private readonly bool $isActive = true,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $platformName = $this->platformName();

        $mail = (new MailMessage)
            ->subject('You were granted access to ' . $this->moduleName)
            ->greeting($this->displayName($notifiable) . ',')
            ->line('Your existing platform account can now be used with the ' . $this->moduleName . '.')
            ->line('This is an automated system message from ' . $platformName . '.')
            ->line('Use your existing account credentials to sign in to the system.');

        if ($this->departmentName) {
            $mail->line('Assigned Department: ' . $this->departmentName);
        }

        if ($this->roleName) {
            $mail->line('Assigned Role: ' . $this->roleName);
        }

        $mail->line('Module Access Status: ' . ($this->isActive ? 'Active' : 'Inactive'));

        if (! $this->isActive) {
            $mail->line('This module assignment is currently inactive until your administrator enables it.');
        }

        return $mail
            ->action('Login to System', route('login'))
            ->line('If you were not expecting this access update, please report it to your system administrator immediately.')
            ->line('Do not reply to this email.')
            ->salutation($platformName);
    }
}
