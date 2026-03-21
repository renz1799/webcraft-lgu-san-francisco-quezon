<?php

namespace App\Core\Services\Contracts\GoogleDrive;

interface GoogleDriveSettingsProviderInterface
{
    public function oauthClientJsonPath(): string;

    public function redirectUri(): ?string;

    public function defaultFolderId(): ?string;

    public function defaultMakePublic(): bool;
}
