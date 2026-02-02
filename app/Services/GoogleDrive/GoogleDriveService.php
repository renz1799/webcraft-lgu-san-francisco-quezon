<?php

namespace App\Services\GoogleDrive;

use App\Services\Contracts\GoogleDriveServiceInterface;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class GoogleDriveService implements GoogleDriveServiceInterface
{
    private GoogleDrive $drive;
    private string $folderId;
    private bool $defaultMakePublic;

    public function __construct()
    {
        $serviceAccountPath = config('services.google_drive.service_account_json');
        $this->folderId = (string) config('services.google_drive.folder_id');
        $this->defaultMakePublic = (bool) config('services.google_drive.make_public', false);

        if (!$serviceAccountPath || !$this->folderId) {
            throw new \RuntimeException('Google Drive config missing. Check services.google_drive.* and .env.');
        }

        $absolutePath = base_path($serviceAccountPath);
        if (!is_file($absolutePath)) {
            throw new \RuntimeException("Google service account JSON not found at: {$absolutePath}");
        }

        // Guard: ensure this is really a service account json, not OAuth client secret
        $auth = json_decode(file_get_contents($absolutePath), true);
        if (($auth['type'] ?? null) !== 'service_account') {
            throw new \RuntimeException('Expected service_account JSON (type=service_account). You provided a non-service account credentials file.');
        }

        $client = new GoogleClient();
        $client->setApplicationName(config('app.name', 'Laravel'));
        $client->setAuthConfig($absolutePath);
        $client->setScopes([GoogleDrive::DRIVE]);

        $this->drive = new GoogleDrive($client);
    }

    public function upload(UploadedFile $file, ?string $name = null, bool $makePublic = false): array
    {
        $finalName = $name ?: $this->safeFileName($file);
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $makePublic = $makePublic ?: $this->defaultMakePublic;

        $driveFile = new DriveFile([
            'name' => $finalName,
            'parents' => [$this->folderId],
            'mimeType' => $mimeType,
        ]);

        $created = $this->drive->files->create($driveFile, [
            'data' => file_get_contents($file->getRealPath()),
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'supportsAllDrives' => true,
            'fields' => 'id,name,mimeType,size,webViewLink,webContentLink,createdTime',
        ]);

        Log::info('[GoogleDrive] file created', [
            'file_id' => $created->id,
            'name' => $created->name,
            'folder_id' => $this->folderId,
            'make_public' => $makePublic,
        ]);

        if ($makePublic) {
            $this->makePublicReadable($created->id);

            $created = $this->drive->files->get($created->id, [
                'supportsAllDrives' => true,
                'fields' => 'id,name,mimeType,size,webViewLink,webContentLink,createdTime',
            ]);
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
            'folder_id' => $this->folderId,
        ];
    }

    public function uploadImage(UploadedFile $file, ?string $name = null, bool $makePublic = false): array
    {
        $mime = (string) $file->getMimeType();
        if (!str_starts_with($mime, 'image/')) {
            throw new \InvalidArgumentException("Expected image/* mime type, got: {$mime}");
        }

        return $this->upload($file, $name, $makePublic);
    }

    public function uploadPdf(UploadedFile $file, ?string $name = null, bool $makePublic = false): array
    {
        $mime = (string) $file->getMimeType();
        if ($mime !== 'application/pdf') {
            throw new \InvalidArgumentException("Expected application/pdf mime type, got: {$mime}");
        }

        return $this->upload($file, $name, $makePublic);
    }

    public function delete(string $fileId): void
    {
        $this->drive->files->delete($fileId, [
            'supportsAllDrives' => true,
        ]);
    }

    public function get(string $fileId): array
    {
        $f = $this->drive->files->get($fileId, [
            'supportsAllDrives' => true,
            'fields' => 'id,name,mimeType,size,webViewLink,webContentLink,createdTime,trashed',
        ]);

        return [
            'drive_file_id' => $f->id,
            'name' => $f->name,
            'mime_type' => $f->mimeType,
            'size' => (int) ($f->size ?? 0),
            'web_view_link' => $f->webViewLink,
            'web_content_link' => $f->webContentLink,
            'created_time' => $f->createdTime,
            'trashed' => (bool) ($f->trashed ?? false),
        ];
    }

    private function makePublicReadable(string $fileId): void
    {
        $permission = new Permission([
            'type' => 'anyone',
            'role' => 'reader',
        ]);

        try {
            Log::info('[GoogleDrive] setting public permission', ['file_id' => $fileId]);

            $this->drive->permissions->create($fileId, $permission, [
                'supportsAllDrives' => true,
                'fields' => 'id',
            ]);
        } catch (\Throwable $e) {
            Log::warning('[GoogleDrive] Failed to set public permission', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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
}
