<?php

namespace App\Core\Services\GoogleDrive;

use App\Core\Repositories\Contracts\GoogleTokenRepositoryInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveClientFactoryInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveConnectionServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Support\Facades\Crypt;

class GoogleDriveConnectionService implements GoogleDriveConnectionServiceInterface
{
    public function __construct(
        private readonly GoogleTokenRepositoryInterface $tokens,
        private readonly GoogleDriveClientFactoryInterface $clientFactory,
        private readonly CurrentContext $context,
    ) {}

    public function isConnected(): bool
    {
        [$moduleId, $departmentId] = $this->scope(false);

        if ($moduleId === null || $departmentId === null) {
            return false;
        }

        $stored = $this->tokens->findForContext($moduleId, $departmentId);

        return (bool) ($stored && $stored->refresh_token);
    }

    public function getAuthUrl(): string
    {
        $this->scope();

        $client = $this->clientFactory->makeClient();
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client->createAuthUrl();
    }

    public function handleCallback(string $code, ?string $connectedByUserId = null): void
    {
        $client = $this->clientFactory->makeClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (! empty($token['error'])) {
            throw new \RuntimeException('Google OAuth error: ' . ($token['error_description'] ?? $token['error']));
        }

        [$moduleId, $departmentId] = $this->scope();

        $stored = $this->tokens->findForContext($moduleId, $departmentId);
        $existingRefreshToken = ($stored && $stored->refresh_token)
            ? Crypt::decryptString((string) $stored->refresh_token)
            : null;

        $this->tokens->upsertForContext($moduleId, $departmentId, [
            'connected_by_user_id' => $connectedByUserId,
            'access_token' => $token['access_token'] ?? null,
            'refresh_token' => $token['refresh_token'] ?? $existingRefreshToken,
            'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
        ]);
    }

    public function disconnect(): void
    {
        [$moduleId, $departmentId] = $this->scope();

        $this->tokens->deleteForContext($moduleId, $departmentId);
    }

    private function scope(bool $require = true): array
    {
        $moduleId = trim((string) ($this->context->moduleId() ?? ''));
        $departmentId = trim((string) ($this->context->defaultDepartmentId() ?? ''));

        if (! $require) {
            return [
                $moduleId !== '' ? $moduleId : null,
                $departmentId !== '' ? $departmentId : null,
            ];
        }

        if ($moduleId === '' || $departmentId === '') {
            throw new \RuntimeException('Google Drive context is not available.');
        }

        return [$moduleId, $departmentId];
    }
}
