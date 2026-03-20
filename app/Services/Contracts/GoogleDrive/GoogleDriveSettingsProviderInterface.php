<?php

namespace App\Services\Contracts\GoogleDrive;

interface GoogleDriveSettingsProviderInterface
{
    public function oauthClientJsonPath(): string;

    public function redirectUri(): ?string;

    public function defaultFolderId(): ?string;

    public function defaultMakePublic(): bool;
}
