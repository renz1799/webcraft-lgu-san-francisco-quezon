<?php

namespace App\Core\Services\GoogleDrive;

use App\Core\Builders\Contracts\GoogleDrive\GoogleDriveFolderNameSanitizerInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveClientFactoryInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use Google\Service\Drive\DriveFile;

class GoogleDriveFolderService implements GoogleDriveFolderServiceInterface
{
    public function __construct(
        private readonly GoogleDriveClientFactoryInterface $clientFactory,
        private readonly GoogleDriveFolderNameSanitizerInterface $folderNameSanitizer,
    ) {}

    public function sanitizeFolderName(string $value): string
    {
        return $this->folderNameSanitizer->sanitize($value);
    }

    public function findFolder(string $name, string $parentId): ?array
    {
        $resolvedParentId = trim($parentId);

        if ($resolvedParentId === '') {
            throw new \RuntimeException('Missing Google Drive parent folder id.');
        }

        $drive = $this->clientFactory->makeAuthorizedDrive();
        $folderName = $this->sanitizeFolderName($name);
        $existing = $this->findChildFolder($drive, $folderName, $resolvedParentId);

        if ($existing === null) {
            return null;
        }

        return [
            'drive_folder_id' => (string) ($existing->id ?? ''),
            'name' => (string) ($existing->name ?? $folderName),
            'created' => false,
            'parent_id' => $resolvedParentId,
        ];
    }

    public function ensureFolder(string $name, string $parentId): array
    {
        $resolvedParentId = trim($parentId);

        if ($resolvedParentId === '') {
            throw new \RuntimeException('Missing Google Drive parent folder id.');
        }

        $folderName = $this->sanitizeFolderName($name);

        $existing = $this->findFolder($folderName, $resolvedParentId);

        if ($existing !== null) {
            return $existing;
        }

        $drive = $this->clientFactory->makeAuthorizedDrive();

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

    private function escapeQueryValue(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "\\'"], $value);
    }
}
