<?php

namespace App\Services\GoogleDrive;

use App\Repositories\Contracts\GoogleTokenRepositoryInterface;
use App\Services\Contracts\GoogleDriveGlobalServiceInterface;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;

class GoogleDriveGlobalService implements GoogleDriveGlobalServiceInterface
{
    public function __construct(
        private readonly GoogleTokenRepositoryInterface $tokens,
    ) {}

    public function isConnected(): bool
    {
        $stored = $this->tokens->getGlobal();
        return (bool) ($stored && $stored->refresh_token);
    }

    public function getAuthUrl(): string
    {
        $client = $this->makeClient();
        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return $client->createAuthUrl();
    }

    public function handleCallback(string $code): void
    {
        $client = $this->makeClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (!empty($token['error'])) {
            throw new \RuntimeException('Google OAuth error: ' . ($token['error_description'] ?? $token['error']));
        }

        $this->tokens->upsertGlobal([
            'access_token' => $token['access_token'] ?? null,
            'refresh_token' => $token['refresh_token'] ?? null,
            'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
        ]);
    }

    public function upload(UploadedFile $file, ?string $name = null, bool $makePublic = false): array
    {
        if (!$this->isConnected()) {
            throw new \RuntimeException('Google Drive not connected. Ask an admin to connect Google Drive.');
        }

        $client = $this->makeClient();
        $stored = $this->tokens->getGlobal();

        $refreshToken = Crypt::decryptString($stored->refresh_token);
        $client->refreshToken($refreshToken);

        $drive = new GoogleDrive($client);

        $folderId = (string) config('services.google_drive.folder_id');
        $makePublic = (bool) ($makePublic ?: config('services.google_drive.make_public', false));

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

    public function disconnect(): void
    {
        $this->tokens->deleteGlobal();
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

    public function download(string $fileId): array
    {
        if (!$this->isConnected()) {
            throw new \RuntimeException('Google Drive not connected. Ask an admin to connect Google Drive.');
        }

        $client = $this->makeClient();
        $stored = $this->tokens->getGlobal();

        $refreshToken = \Illuminate\Support\Facades\Crypt::decryptString($stored->refresh_token);
        $client->refreshToken($refreshToken);

        $drive = new \Google\Service\Drive($client);

        // Metadata
        $meta = $drive->files->get($fileId, [
            'fields' => 'id,name,mimeType,size',
            'supportsAllDrives' => true,
        ]);

        // Raw bytes
        $resp = $drive->files->get($fileId, [
            'alt' => 'media',
            'supportsAllDrives' => true,
        ]);

        $bytes = (string) $resp->getBody();

        return [
            'name' => $meta->name,
            'mime_type' => $meta->mimeType,
            'bytes' => $bytes,
        ];
    }

}
