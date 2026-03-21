<?php

namespace App\Core\Services\GoogleDrive;

use App\Core\Builders\Contracts\GoogleDrive\GoogleDriveFileMetadataBuilderInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveClientFactoryInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveSettingsProviderInterface;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use Illuminate\Http\UploadedFile;

class GoogleDriveFileService implements GoogleDriveFileServiceInterface
{
    public function __construct(
        private readonly GoogleDriveClientFactoryInterface $clientFactory,
        private readonly GoogleDriveSettingsProviderInterface $settings,
        private readonly GoogleDriveFileMetadataBuilderInterface $fileMetadataBuilder,
    ) {}

    public function upload(
        UploadedFile $file,
        ?string $name = null,
        bool $makePublic = false,
        ?string $folderId = null,
    ): array {
        $drive = $this->clientFactory->makeAuthorizedDrive();
        $targetFolderId = $this->resolveUploadFolderId($folderId);
        $finalName = $name ?: $this->safeFileName($file);
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $shouldMakePublic = $makePublic || $this->settings->defaultMakePublic();

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

        if ($shouldMakePublic) {
            $this->makeFilePublic($drive, (string) $created->id);
        }

        return $this->fileMetadataBuilder->build($created, $targetFolderId, $shouldMakePublic);
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

        $drive = $this->clientFactory->makeAuthorizedDrive();

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

        return $this->fileMetadataBuilder->build(
            $copied,
            $resolvedTargetFolderId !== '' ? $resolvedTargetFolderId : null,
            false,
            ['source_file_id' => $resolvedSourceFileId],
        );
    }

    public function deleteFile(string $fileId): void
    {
        $resolvedFileId = trim($fileId);

        if ($resolvedFileId === '') {
            throw new \RuntimeException('Missing Google Drive file id.');
        }

        $drive = $this->clientFactory->makeAuthorizedDrive();

        $drive->files->delete($resolvedFileId, [
            'supportsAllDrives' => true,
        ]);
    }

    public function download(string $fileId): array
    {
        $resolvedFileId = trim($fileId);

        if ($resolvedFileId === '') {
            throw new \RuntimeException('Missing Google Drive file id.');
        }

        $drive = $this->clientFactory->makeAuthorizedDrive();

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

    private function resolveUploadFolderId(?string $folderId): string
    {
        $resolvedFolderId = trim((string) ($folderId ?: $this->settings->defaultFolderId()));

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

    private function makeFilePublic(object $drive, string $fileId): void
    {
        $permission = new Permission(['type' => 'anyone', 'role' => 'reader']);

        $drive->permissions->create($fileId, $permission, [
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);
    }
}
