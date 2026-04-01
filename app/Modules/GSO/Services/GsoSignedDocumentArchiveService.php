<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\GsoStorageSettingsServiceInterface;
use RuntimeException;

class GsoSignedDocumentArchiveService implements GsoSignedDocumentArchiveServiceInterface
{
    private const ALLOWED_TYPES = [
        'AIR',
        'RIS',
        'PAR',
        'ICS',
        'PTR',
        'ITR',
        'WMR',
    ];

    public function __construct(
        private readonly GsoStorageSettingsServiceInterface $storageSettings,
        private readonly GoogleDriveFolderServiceInterface $driveFolders,
        private readonly GoogleDriveFileServiceInterface $driveFiles,
    ) {}

    public function archive(string $documentType, string $documentNumber, string $pdfPath): array
    {
        [$resolvedType, $resolvedNumber] = $this->normalizeDocumentReference($documentType, $documentNumber);

        if (! is_file($pdfPath) || ! is_readable($pdfPath)) {
            throw new RuntimeException('Generated PDF file is missing or unreadable.');
        }

        $documentFolderId = $this->resolveDocumentFolderId($resolvedType, $resolvedNumber, true);

        $fileName = $resolvedNumber . '.pdf';
        $stored = $this->driveFiles->replaceFileInFolder(
            path: $pdfPath,
            name: $fileName,
            folderId: $documentFolderId,
            mimeType: 'application/pdf',
            makePublic: false,
        );

        return $stored + [
            'document_type' => $resolvedType,
            'document_number' => $resolvedNumber,
            'file_name' => $stored['name'] ?? $fileName,
            'folder_path' => implode(' / ', [$resolvedType, $resolvedNumber]),
            'folder_id' => $documentFolderId,
        ];
    }

    public function findArchived(string $documentType, string $documentNumber): ?array
    {
        [$resolvedType, $resolvedNumber] = $this->normalizeDocumentReference($documentType, $documentNumber);
        $documentFolderId = $this->resolveDocumentFolderId($resolvedType, $resolvedNumber, false);

        if ($documentFolderId === null) {
            return null;
        }

        $fileName = $resolvedNumber . '.pdf';
        $file = $this->driveFiles->findFileInFolder($fileName, $documentFolderId);

        if ($file === null) {
            return null;
        }

        return $file + [
            'document_type' => $resolvedType,
            'document_number' => $resolvedNumber,
            'file_name' => $file['name'] ?? $fileName,
            'folder_path' => implode(' / ', [$resolvedType, $resolvedNumber]),
            'folder_id' => $documentFolderId,
        ];
    }

    public function downloadArchived(string $documentType, string $documentNumber): array
    {
        $archived = $this->findArchived($documentType, $documentNumber);
        $driveFileId = trim((string) ($archived['drive_file_id'] ?? ''));

        if ($driveFileId === '') {
            throw new RuntimeException('Signed PDF is not yet uploaded for this document.');
        }

        return $this->driveFiles->download($driveFileId);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function normalizeDocumentReference(string $documentType, string $documentNumber): array
    {
        $resolvedType = strtoupper(trim($documentType));
        $resolvedNumber = trim($documentNumber);

        if (! in_array($resolvedType, self::ALLOWED_TYPES, true)) {
            throw new RuntimeException('Unsupported signed document type.');
        }

        if ($resolvedNumber === '') {
            throw new RuntimeException('Document number is required before archiving the PDF.');
        }

        return [$resolvedType, $resolvedNumber];
    }

    private function resolveDocumentFolderId(string $documentType, string $documentNumber, bool $create): ?string
    {
        $rootFolderId = trim((string) ($this->storageSettings->signedDocumentsRootFolderId() ?? ''));

        if ($rootFolderId === '') {
            return $create
                ? throw new RuntimeException('GSO signed documents root folder is not configured.')
                : null;
        }

        $typeFolder = $create
            ? $this->driveFolders->ensureFolder($documentType, $rootFolderId)
            : $this->driveFolders->findFolder($documentType, $rootFolderId);

        $typeFolderId = trim((string) ($typeFolder['drive_folder_id'] ?? ''));

        if ($typeFolderId === '') {
            if ($create) {
                throw new RuntimeException('Failed to resolve the Google Drive document-type folder.');
            }

            return null;
        }

        $documentFolder = $create
            ? $this->driveFolders->ensureFolder($documentNumber, $typeFolderId)
            : $this->driveFolders->findFolder($documentNumber, $typeFolderId);

        $documentFolderId = trim((string) ($documentFolder['drive_folder_id'] ?? ''));

        if ($documentFolderId === '') {
            if ($create) {
                throw new RuntimeException('Failed to resolve the Google Drive document folder.');
            }

            return null;
        }

        return $documentFolderId;
    }
}
