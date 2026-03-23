<?php

namespace App\Core\Notifications\Concerns;

trait ResolvesPlatformMailBranding
{
    protected function platformName(): string
    {
        $name = trim((string) (config('mail.from.name') ?: config('app.name') ?: 'Webcraft LGU Platform'));

        return $name !== '' ? $name : 'Webcraft LGU Platform';
    }

    protected function displayName(object $notifiable): string
    {
        $displayName = trim((string) ($notifiable->profile?->full_name ?: $notifiable->username ?: $notifiable->email ?: ''));

        return $displayName !== '' ? $displayName : 'Hello';
    }
}
