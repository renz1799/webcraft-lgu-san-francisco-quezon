<?php

namespace App\Core\Services\GoogleDrive;

use App\Core\Services\Contracts\GoogleDrive\GoogleDriveSettingsProviderInterface;

class GoogleDriveSettingsProvider implements GoogleDriveSettingsProviderInterface
{
    public function oauthClientJsonPath(): string
    {
        $jsonPath = config('services.google_drive.oauth_client_json');

        if (! $jsonPath) {
            throw new \RuntimeException('Missing services.google_drive.oauth_client_json config.');
        }

        $absolutePath = base_path($jsonPath);

        if (! is_file($absolutePath)) {
            throw new \RuntimeException("OAuth client json not found: {$absolutePath}");
        }

        return $absolutePath;
    }

    public function redirectUri(): ?string
    {
        return config('services.google_drive.redirect_uri');
    }

    public function defaultFolderId(): ?string
    {
        $folderId = trim((string) config('services.google_drive.folder_id'));

        return $folderId !== '' ? $folderId : null;
    }

    public function defaultMakePublic(): bool
    {
        return (bool) config('services.google_drive.make_public', false);
    }
}
