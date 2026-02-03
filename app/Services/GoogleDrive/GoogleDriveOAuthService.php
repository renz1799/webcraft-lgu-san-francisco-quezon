<?php

namespace App\Services\GoogleDrive;

use App\Repositories\Contracts\GoogleTokenRepositoryInterface;
use App\Services\Contracts\GoogleDriveOAuthServiceInterface;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;

class GoogleDriveOAuthService implements GoogleDriveOAuthServiceInterface
{
    public function __construct(
        private readonly GoogleTokenRepositoryInterface $tokens,
    ) {}

    public function isConnected(string $userId): bool
    {
        $stored = $this->tokens->findForUser($userId);

        return (bool) ($stored && $stored->refresh_token);
    }

    public function getAuthUrl(string $userId): string
    {
        $client = $this->makeClient();

        // Important: offline to get refresh_token
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        // Use state to link callback to user
        $client->setState($userId);

        return $client->createAuthUrl();
    }

    public function handleCallback(string $userId, string $code): void
    {
        $client = $this->makeClient();

        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (!empty($token['error'])) {
            throw new \RuntimeException('Google OAuth error: ' . ($token['error_description'] ?? $token['error']));
        }

        $this->tokens->upsertForUser($userId, [
            'access_token' => $token['access_token'] ?? null,
            'refresh_token' => $token['refresh_token'] ?? null, // only returned first consent
            'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
        ]);
    }

    public function upload(string $userId, UploadedFile $file, ?string $name = null, bool $makePublic = false): array
    {
        $client = $this->makeClient();

        $stored = $this->tokens->findForUser($userId);
        if (!$stored || !$stored->refresh_token) {
            throw new \RuntimeException('Google Drive not connected. Please connect your Google account first.');
        }

        $refreshToken = Crypt::decryptString($stored->refresh_token);

        $client->refreshToken($refreshToken);

        $drive = new GoogleDrive($client);

        $folderId = (string) config('services.google_drive.folder_id');
        $finalName = $name ?: $this->safeFileName($file);
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $driveFile = new DriveFile([
            'name' => $finalName,
            'parents' => [$folderId],
            'mimeType' => $mimeType,
        ]);

        $created = $drive->files->create($driveFile, [
            'data' => file_get_contents($file->getRealPath()),
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id,name,mimeType,size,webViewLink,webContentLink,createdTime',
        ]);

        if ($makePublic) {
            $permission = new Permission(['type' => 'anyone', 'role' => 'reader']);
            $drive->permissions->create($created->id, $permission, ['fields' => 'id']);
        }

        return [
            'drive_file_id' => $created->id,
            'name' => $created->name,
            'mime_type' => $created->mimeType,
            'size' => (int) ($created->size ?? 0),
            'web_view_link' => $created->webViewLink,
            'web_content_link' => $created->webContentLink,
            'created_time' => $created->createdTime,
            'is_public' => $makePublic,
            'folder_id' => $folderId,
        ];
    }

    private function makeClient(): GoogleClient
    {
        $jsonPath = config('services.google_drive.oauth_client_json');
        if (!$jsonPath) {
            throw new \RuntimeException('Missing services.google_drive.oauth_client_json config.');
        }

        $absolutePath = base_path($jsonPath);
        if (!is_file($absolutePath)) {
            throw new \RuntimeException("OAuth client json not found: {$absolutePath}");
        }

        $client = new GoogleClient();
        $client->setAuthConfig($absolutePath);
        $client->setRedirectUri(config('services.google_drive.redirect_uri'));
        $client->setScopes([GoogleDrive::DRIVE]);

        return $client;
    }

    private function safeFileName(UploadedFile $file): string
    {
        $original = $file->getClientOriginalName();
        $ext = $file->getClientOriginalExtension();

        $base = pathinfo($original, PATHINFO_FILENAME);
        $base = preg_replace('/[^a-zA-Z0-9._-]+/', '-', $base) ?: 'file';

        $stamp = now()->format('Ymd_His');
        $rand = bin2hex(random_bytes(4));

        return $ext ? "{$base}_{$stamp}_{$rand}.{$ext}" : "{$base}_{$stamp}_{$rand}";
    }

    public function download(string $userId, string $fileId): array
    {
    $client = $this->makeClient();

    $stored = $this->tokens->findForUser($userId);
    if (!$stored || !$stored->refresh_token) {
        throw new \RuntimeException('Google Drive not connected. Please connect your Google account first.');
    }

    $refreshToken = \Illuminate\Support\Facades\Crypt::decryptString($stored->refresh_token);
    $client->refreshToken($refreshToken);

    $drive = new \Google\Service\Drive($client);

    // metadata (mime + name)
    $meta = $drive->files->get($fileId, [
        'fields' => 'id,name,mimeType,size',
        'supportsAllDrives' => true,
    ]);

    // content
    $resp = $drive->files->get($fileId, [
        'alt' => 'media',
        'supportsAllDrives' => true,
    ]);

    // google client returns PSR7 response in some cases; safest:
    $body = (string) $resp->getBody();

    return [
        'name' => $meta->name,
        'mime_type' => $meta->mimeType,
        'bytes' => $body,
    ];
    }

}
