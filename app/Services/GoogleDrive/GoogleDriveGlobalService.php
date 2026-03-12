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
use Illuminate\Support\Str;

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

        if (! empty($token['error'])) {
            throw new \RuntimeException('Google OAuth error: ' . ($token['error_description'] ?? $token['error']));
        }

        $stored = $this->tokens->getGlobal();
        $existingRefreshToken = ($stored && $stored->refresh_token)
            ? Crypt::decryptString($stored->refresh_token)
            : null;

        $this->tokens->upsertGlobal([
            'access_token' => $token['access_token'] ?? null,
            'refresh_token' => $token['refresh_token'] ?? $existingRefreshToken,
            'expires_at' => isset($token['expires_in']) ? now()->addSeconds((int) $token['expires_in']) : null,
        ]);
    }

    public function sanitizeFolderName(string $value): string
    {
        $normalized = Str::ascii($value);
        $normalized = preg_replace('/[^A-Za-z0-9 ._()-]+/', '-', $normalized) ?? '';
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? '';
        $normalized = preg_replace('/-+/', '-', $normalized) ?? '';
        $normalized = trim($normalized, " .-\t\n\r\0\x0B");

        return $normalized !== '' ? $normalized : 'folder';
    }

    public function ensureFolder(string $name, string $parentId): array
    {
        $resolvedParentId = trim($parentId);
        if ($resolvedParentId === '') {
            throw new \RuntimeException('Missing Google Drive parent folder id.');
        }

        $drive = $this->makeAuthorizedDrive();
        $folderName = $this->sanitizeFolderName($name);

        $existing = $this->findChildFolder($drive, $folderName, $resolvedParentId);
        if ($existing !== null) {
            return [
                'drive_folder_id' => (string) ($existing->id ?? ''),
                'name' => (string) ($existing->name ?? $folderName),
                'created' => false,
                'parent_id' => $resolvedParentId,
            ];
        }

        $folder = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$resolvedParentId],
        ]);

        $created = $drive->files->create($folder, [
            'fields' => 'id,name,parents',
            'supportsAllDrives' => true,
        ]);

        return [
            'drive_folder_id' => (string) ($created->id ?? ''),
            'name' => (string) ($created->name ?? $folderName),
            'created' => true,
            'parent_id' => $resolvedParentId,
        ];
    }

    public function upload(
        UploadedFile $file,
        ?string $name = null,
        bool $makePublic = false,
        ?string $folderId = null,
    ): array {
        $drive = $this->makeAuthorizedDrive();
        $targetFolderId = $this->resolveUploadFolderId($folderId);
        $finalName = $name ?: $this->safeFileName($file);
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $makePublic = (bool) ($makePublic ?: config('services.google_drive.make_public', false));

        $driveFile = new DriveFile([
            'name' => $finalName,
            'parents' => [$targetFolderId],
            'mimeType' => $mimeType,
        ]);

        $created = $drive->files->create($driveFile, [
            'data' => file_get_contents($file->getRealPath()),
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id,name,mimeType,size,webViewLink,webContentLink,createdTime,parents',
            'supportsAllDrives' => true,
        ]);

        if ($makePublic) {
            $this->makeFilePublic($drive, (string) $created->id);
        }

        return $this->mapDriveFileMetadata($created, $targetFolderId, $makePublic);
    }

    public function copyFile(
        string $sourceFileId,
        ?string $newName = null,
        ?string $targetFolderId = null,
    ): array {
        $resolvedSourceFileId = trim($sourceFileId);
        if ($resolvedSourceFileId === '') {
            throw new \RuntimeException('Missing Google Drive source file id.');
        }

        $drive = $this->makeAuthorizedDrive();

        $source = $drive->files->get($resolvedSourceFileId, [
            'fields' => 'id,name,mimeType,size,webViewLink,webContentLink,createdTime,parents',
            'supportsAllDrives' => true,
        ]);

        $resolvedTargetFolderId = trim((string) $targetFolderId);
        if ($resolvedTargetFolderId === '') {
            $resolvedTargetFolderId = (string) (($source->parents[0] ?? ''));
        }

        $copyBody = [
            'name' => $newName ?: (string) ($source->name ?? 'copy'),
        ];

        if ($resolvedTargetFolderId !== '') {
            $copyBody['parents'] = [$resolvedTargetFolderId];
        }

        $copied = $drive->files->copy(
            $resolvedSourceFileId,
            new DriveFile($copyBody),
            [
                'fields' => 'id,name,mimeType,size,webViewLink,webContentLink,createdTime,parents',
                'supportsAllDrives' => true,
            ],
        );

        return $this->mapDriveFileMetadata($copied, $resolvedTargetFolderId !== '' ? $resolvedTargetFolderId : null, false, [
            'source_file_id' => $resolvedSourceFileId,
        ]);
    }

    public function deleteFile(string $fileId): void
    {
        $resolvedFileId = trim($fileId);
        if ($resolvedFileId === '') {
            throw new \RuntimeException('Missing Google Drive file id.');
        }

        $drive = $this->makeAuthorizedDrive();

        $drive->files->delete($resolvedFileId, [
            'supportsAllDrives' => true,
        ]);
    }

    public function disconnect(): void
    {
        $this->tokens->deleteGlobal();
    }

    public function download(string $fileId): array
    {
        $resolvedFileId = trim($fileId);
        if ($resolvedFileId === '') {
            throw new \RuntimeException('Missing Google Drive file id.');
        }

        $drive = $this->makeAuthorizedDrive();

        $meta = $drive->files->get($resolvedFileId, [
            'fields' => 'id,name,mimeType,size',
            'supportsAllDrives' => true,
        ]);

        $resp = $drive->files->get($resolvedFileId, [
            'alt' => 'media',
            'supportsAllDrives' => true,
        ]);

        return [
            'name' => (string) ($meta->name ?? 'download'),
            'mime_type' => (string) ($meta->mimeType ?? 'application/octet-stream'),
            'bytes' => (string) $resp->getBody(),
        ];
    }

    protected function makeAuthorizedDrive()
    {
        return new GoogleDrive($this->makeAuthorizedClient());
    }

    protected function makeAuthorizedClient(): GoogleClient
    {
        $stored = $this->tokens->getGlobal();
        if (! $stored || ! $stored->refresh_token) {
            throw new \RuntimeException('Google Drive not connected. Ask an admin to connect Google Drive.');
        }

        $client = $this->makeClient();
        $refreshToken = Crypt::decryptString($stored->refresh_token);
        $client->refreshToken($refreshToken);

        return $client;
    }

    private function makeClient(): GoogleClient
    {
        $jsonPath = config('services.google_drive.oauth_client_json');
        if (! $jsonPath) {
            throw new \RuntimeException('Missing services.google_drive.oauth_client_json config.');
        }

        $absolutePath = base_path($jsonPath);
        if (! is_file($absolutePath)) {
            throw new \RuntimeException("OAuth client json not found: {$absolutePath}");
        }

        $client = new GoogleClient();
        $client->setAuthConfig($absolutePath);
        $client->setRedirectUri(config('services.google_drive.redirect_uri'));
        $client->setScopes([GoogleDrive::DRIVE]);

        return $client;
    }

    private function resolveUploadFolderId(?string $folderId): string
    {
        $resolvedFolderId = trim((string) ($folderId ?: config('services.google_drive.folder_id')));
        if ($resolvedFolderId === '') {
            throw new \RuntimeException('Missing Google Drive target folder id.');
        }

        return $resolvedFolderId;
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

    private function mapDriveFileMetadata(object $file, ?string $folderId = null, bool $isPublic = false, array $extra = []): array
    {
        $resolvedFolderId = $folderId;
        if ($resolvedFolderId === null || $resolvedFolderId === '') {
            $resolvedFolderId = (string) (($file->parents[0] ?? ''));
        }

        return array_merge([
            'drive_file_id' => (string) ($file->id ?? ''),
            'name' => (string) ($file->name ?? ''),
            'mime_type' => (string) ($file->mimeType ?? 'application/octet-stream'),
            'size' => (int) ($file->size ?? 0),
            'web_view_link' => $file->webViewLink ?? null,
            'web_content_link' => $file->webContentLink ?? null,
            'created_time' => $file->createdTime ?? null,
            'is_public' => $isPublic,
            'folder_id' => $resolvedFolderId,
        ], $extra);
    }

    private function findChildFolder(object $drive, string $name, string $parentId): ?object
    {
        $query = sprintf(
            "'%s' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false and name = '%s'",
            $this->escapeQueryValue($parentId),
            $this->escapeQueryValue($name),
        );

        $response = $drive->files->listFiles([
            'q' => $query,
            'pageSize' => 1,
            'fields' => 'files(id,name,parents)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        $files = method_exists($response, 'getFiles')
            ? $response->getFiles()
            : (array) ($response->files ?? []);

        return $files[0] ?? null;
    }

    private function makeFilePublic(object $drive, string $fileId): void
    {
        $permission = new Permission(['type' => 'anyone', 'role' => 'reader']);

        $drive->permissions->create($fileId, $permission, [
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);
    }

    private function escapeQueryValue(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }
}