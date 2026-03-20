<?php

namespace App\Services\GoogleDrive;

use App\Repositories\Contracts\GoogleTokenRepositoryInterface;
use App\Services\Contracts\GoogleDrive\GoogleDriveClientFactoryInterface;
use App\Services\Contracts\GoogleDrive\GoogleDriveSettingsProviderInterface;
use App\Support\CurrentContext;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Illuminate\Support\Facades\Crypt;

class GoogleDriveClientFactory implements GoogleDriveClientFactoryInterface
{
    public function __construct(
        private readonly GoogleTokenRepositoryInterface $tokens,
        private readonly GoogleDriveSettingsProviderInterface $settings,
        private readonly CurrentContext $context,
    ) {}

    public function makeClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setAuthConfig($this->settings->oauthClientJsonPath());
        $client->setRedirectUri($this->settings->redirectUri());
        $client->setScopes([GoogleDrive::DRIVE]);

        return $client;
    }

    public function makeAuthorizedClient(): GoogleClient
    {
        $stored = $this->requireToken();

        if (! $stored->refresh_token) {
            throw new \RuntimeException('Google Drive is not connected for the current module context.');
        }

        $client = $this->makeClient();
        $refreshToken = Crypt::decryptString((string) $stored->refresh_token);
        $client->refreshToken($refreshToken);

        return $client;
    }

    public function makeAuthorizedDrive(): GoogleDrive
    {
        return new GoogleDrive($this->makeAuthorizedClient());
    }

    private function requireToken(): object
    {
        [$moduleId, $departmentId] = $this->requireScope();

        $stored = $this->tokens->findForContext($moduleId, $departmentId);

        if ($stored) {
            return $stored;
        }

        throw new \RuntimeException('Google Drive is not connected for the current module context.');
    }

    private function requireScope(): array
    {
        $moduleId = trim((string) ($this->context->moduleId() ?? ''));
        $departmentId = trim((string) ($this->context->defaultDepartmentId() ?? ''));

        if ($moduleId === '' || $departmentId === '') {
            throw new \RuntimeException('Google Drive context is not available.');
        }

        return [$moduleId, $departmentId];
    }
}
