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
        $resolvedType = strtoupper(trim($documentType));
        $resolvedNumber = trim($documentNumber);

        if (! in_array($resolvedType, self::ALLOWED_TYPES, true)) {
            throw new RuntimeException('Unsupported signed document type.');
        }

        if ($resolvedNumber === '') {
            throw new RuntimeException('Document number is required before archiving the PDF.');
        }

        if (! is_file($pdfPath) || ! is_readable($pdfPath)) {
            throw new RuntimeException('Generated PDF file is missing or unreadable.');
        }

        $rootFolderId = trim((string) ($this->storageSettings->signedDocumentsRootFolderId() ?? ''));

        if ($rootFolderId === '') {
            throw new RuntimeException('GSO signed documents root folder is not configured.');
        }

        $typeFolder = $this->driveFolders->ensureFolder($resolvedType, $rootFolderId);
        $typeFolderId = trim((string) ($typeFolder['drive_folder_id'] ?? ''));

        if ($typeFolderId === '') {
            throw new RuntimeException('Failed to resolve the Google Drive document-type folder.');
        }

        $documentFolder = $this->driveFolders->ensureFolder($resolvedNumber, $typeFolderId);
        $documentFolderId = trim((string) ($documentFolder['drive_folder_id'] ?? ''));

        if ($documentFolderId === '') {
            throw new RuntimeException('Failed to resolve the Google Drive document folder.');
        }

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
}
