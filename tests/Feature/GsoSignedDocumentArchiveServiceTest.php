<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Services\Contracts\GsoStorageSettingsServiceInterface;
use App\Modules\GSO\Services\GsoSignedDocumentArchiveService;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Tests\TestCase;

class GsoSignedDocumentArchiveServiceTest extends TestCase
{
    public function test_archive_uses_signed_documents_root_and_canonical_filename(): void
    {
        $folderService = new FakeGoogleDriveFolderService();
        $fileService = new FakeGoogleDriveFileService();
        $storageSettings = new FakeGsoStorageSettingsService('signed-root-folder');
        $service = new GsoSignedDocumentArchiveService($storageSettings, $folderService, $fileService);

        $path = tempnam(sys_get_temp_dir(), 'gso-signed-');
        file_put_contents($path, '%PDF-1.4 test');

        try {
            $stored = $service->archive('AIR', 'AIR-2026-0001', $path);
        } finally {
            @unlink($path);
        }

        $this->assertSame([
            ['name' => 'AIR', 'parent' => 'signed-root-folder'],
            ['name' => 'AIR-2026-0001', 'parent' => 'folder-air'],
        ], $folderService->calls);

        $this->assertSame([
            'path' => $path,
            'name' => 'AIR-2026-0001.pdf',
            'folderId' => 'folder-air-2026-0001',
            'mimeType' => 'application/pdf',
            'makePublic' => false,
        ], $fileService->lastReplaceCall);

        $this->assertSame('AIR', $stored['document_type']);
        $this->assertSame('AIR-2026-0001', $stored['document_number']);
        $this->assertSame('AIR / AIR-2026-0001', $stored['folder_path']);
        $this->assertSame('AIR-2026-0001.pdf', $stored['file_name']);
        $this->assertTrue($stored['replaced_existing']);
    }

    public function test_archive_requires_configured_root_folder(): void
    {
        $service = new GsoSignedDocumentArchiveService(
            new FakeGsoStorageSettingsService(null),
            new FakeGoogleDriveFolderService(),
            new FakeGoogleDriveFileService(),
        );

        $path = tempnam(sys_get_temp_dir(), 'gso-signed-');
        file_put_contents($path, '%PDF-1.4 test');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('GSO signed documents root folder is not configured.');

            $service->archive('RIS', 'RIS-2026-0001', $path);
        } finally {
            @unlink($path);
        }
    }

    public function test_find_archived_returns_existing_canonical_file_metadata(): void
    {
        $folderService = new FakeGoogleDriveFolderService();
        $fileService = new FakeGoogleDriveFileService();
        $storageSettings = new FakeGsoStorageSettingsService('signed-root-folder');
        $service = new GsoSignedDocumentArchiveService($storageSettings, $folderService, $fileService);

        $archived = $service->findArchived('RIS', 'RIS-2026-0001');

        $this->assertNotNull($archived);
        $this->assertSame('RIS', $archived['document_type']);
        $this->assertSame('RIS-2026-0001', $archived['document_number']);
        $this->assertSame('RIS / RIS-2026-0001', $archived['folder_path']);
        $this->assertSame('RIS-2026-0001.pdf', $archived['file_name']);
        $this->assertSame('drive-file-existing', $archived['drive_file_id']);
    }
}

class FakeGsoStorageSettingsService implements GsoStorageSettingsServiceInterface
{
    public function __construct(
        private readonly ?string $signedRoot,
    ) {}

    public function googleDriveRoots(): array
    {
        return $this->signedRoot !== null
            ? ['signed_documents_root_folder_id' => $this->signedRoot]
            : [];
    }

    public function signedDocumentsRootFolderId(): ?string
    {
        return $this->signedRoot;
    }

    public function inspectionPhotosFolderId(): ?string
    {
        return null;
    }

    public function airUnitFilesFolderId(): ?string
    {
        return null;
    }

    public function airFilesFolderId(): ?string
    {
        return null;
    }

    public function inventoryFilesFolderId(): ?string
    {
        return null;
    }
}

class FakeGoogleDriveFolderService implements GoogleDriveFolderServiceInterface
{
    /**
     * @var array<int, array{name: string, parent: string}>
     */
    public array $calls = [];

    public function sanitizeFolderName(string $value): string
    {
        return $value;
    }

    public function findFolder(string $name, string $parentId): ?array
    {
        if ($name === 'missing') {
            return null;
        }

        return [
            'drive_folder_id' => 'folder-' . strtolower(str_replace(' ', '-', $name)),
            'name' => $name,
            'created' => false,
            'parent_id' => $parentId,
        ];
    }

    public function ensureFolder(string $name, string $parentId): array
    {
        $this->calls[] = [
            'name' => $name,
            'parent' => $parentId,
        ];

        return [
            'drive_folder_id' => 'folder-' . strtolower(str_replace(' ', '-', $name)),
            'name' => $name,
            'created' => true,
            'parent_id' => $parentId,
        ];
    }
}

class FakeGoogleDriveFileService implements GoogleDriveFileServiceInterface
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $lastReplaceCall = null;

    public function findFileInFolder(string $name, string $folderId): ?array
    {
        if ($name === 'missing.pdf') {
            return null;
        }

        return [
            'drive_file_id' => 'drive-file-existing',
            'name' => $name,
            'mime_type' => 'application/pdf',
            'folder_id' => $folderId,
            'created_time' => '2026-04-01T10:00:00+08:00',
        ];
    }

    public function upload(
        UploadedFile $file,
        ?string $name = null,
        bool $makePublic = false,
        ?string $folderId = null,
    ): array {
        throw new RuntimeException('Not used in this test.');
    }

    public function uploadFromPath(
        string $path,
        ?string $name = null,
        ?string $mimeType = null,
        bool $makePublic = false,
        ?string $folderId = null,
    ): array {
        throw new RuntimeException('Not used in this test.');
    }

    public function copyFile(
        string $sourceFileId,
        ?string $newName = null,
        ?string $targetFolderId = null,
    ): array {
        throw new RuntimeException('Not used in this test.');
    }

    public function replaceFileInFolder(
        string $path,
        string $name,
        string $folderId,
        ?string $mimeType = null,
        bool $makePublic = false,
    ): array {
        $this->lastReplaceCall = [
            'path' => $path,
            'name' => $name,
            'folderId' => $folderId,
            'mimeType' => $mimeType,
            'makePublic' => $makePublic,
        ];

        return [
            'drive_file_id' => 'drive-file-1',
            'name' => $name,
            'mime_type' => $mimeType ?? 'application/pdf',
            'folder_id' => $folderId,
            'replaced_count' => 1,
            'replaced_existing' => true,
        ];
    }

    public function deleteFile(string $fileId): void
    {
        throw new RuntimeException('Not used in this test.');
    }

    public function download(string $fileId): array
    {
        throw new RuntimeException('Not used in this test.');
    }
}
