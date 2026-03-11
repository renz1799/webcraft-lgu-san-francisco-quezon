<?php

namespace Tests\Unit;

use App\Repositories\Contracts\GoogleTokenRepositoryInterface;
use App\Services\GoogleDrive\GoogleDriveGlobalService;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class GoogleDriveGlobalServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_sanitize_folder_name_returns_a_drive_safe_human_readable_value(): void
    {
        $service = $this->makeService(new FakeDriveService());

        $this->assertSame('PO-2026-01 Main Office', $service->sanitizeFolderName(" PO/2026:01   Main Office "));
    }

    public function test_ensure_folder_returns_existing_child_folder_without_creating_a_duplicate(): void
    {
        $drive = new FakeDriveService();
        $drive->files->existingFolder = (object) [
            'id' => 'folder-1',
            'name' => 'PO-2026-01',
            'parents' => ['root-folder'],
        ];

        $service = $this->makeService($drive);

        $result = $service->ensureFolder('PO/2026:01', 'root-folder');

        $this->assertSame('folder-1', $result['drive_folder_id']);
        $this->assertSame('PO-2026-01', $result['name']);
        $this->assertFalse($result['created']);
        $this->assertCount(0, $drive->files->createCalls);
        $this->assertStringContainsString("'root-folder' in parents", $drive->files->listOptions[0]['q']);
        $this->assertStringContainsString("name = 'PO-2026-01'", $drive->files->listOptions[0]['q']);
    }

    public function test_ensure_folder_creates_the_child_folder_when_missing(): void
    {
        $drive = new FakeDriveService();
        $drive->files->createResult = (object) [
            'id' => 'folder-2',
            'name' => 'PO-2026-01',
            'parents' => ['root-folder'],
        ];

        $service = $this->makeService($drive);

        $result = $service->ensureFolder('PO/2026:01', 'root-folder');

        $this->assertSame('folder-2', $result['drive_folder_id']);
        $this->assertTrue($result['created']);
        $this->assertCount(1, $drive->files->createCalls);
        $this->assertSame(['root-folder'], $drive->files->createCalls[0]['file']->parents);
    }

    public function test_upload_accepts_a_target_folder_and_can_make_the_file_public(): void
    {
        $drive = new FakeDriveService();
        $drive->files->createResult = (object) [
            'id' => 'file-1',
            'name' => 'photo.jpg',
            'mimeType' => 'image/jpeg',
            'size' => 123,
            'webViewLink' => 'view-link',
            'webContentLink' => 'content-link',
            'createdTime' => '2026-03-11T00:00:00Z',
            'parents' => ['target-folder'],
        ];

        $service = $this->makeService($drive);
        $file = UploadedFile::fake()->create('photo.jpg', 12, 'image/jpeg');

        $result = $service->upload($file, null, true, 'target-folder');

        $this->assertSame('file-1', $result['drive_file_id']);
        $this->assertSame('target-folder', $result['folder_id']);
        $this->assertTrue($result['is_public']);
        $this->assertCount(1, $drive->permissions->createCalls);
        $this->assertSame(['target-folder'], $drive->files->createCalls[0]['file']->parents);
    }

    public function test_copy_file_supports_target_folder_override(): void
    {
        $drive = new FakeDriveService();
        $drive->files->getResults['source-1'] = (object) [
            'id' => 'source-1',
            'name' => 'inspection-photo.jpg',
            'mimeType' => 'image/jpeg',
            'size' => 321,
            'webViewLink' => 'source-view',
            'webContentLink' => 'source-content',
            'createdTime' => '2026-03-11T00:00:00Z',
            'parents' => ['inspection-folder'],
        ];
        $drive->files->copyResult = (object) [
            'id' => 'copy-1',
            'name' => 'inventory-photo.jpg',
            'mimeType' => 'image/jpeg',
            'size' => 321,
            'webViewLink' => 'copy-view',
            'webContentLink' => 'copy-content',
            'createdTime' => '2026-03-11T00:01:00Z',
            'parents' => ['inventory-folder'],
        ];

        $service = $this->makeService($drive);

        $result = $service->copyFile('source-1', 'inventory-photo.jpg', 'inventory-folder');

        $this->assertSame('copy-1', $result['drive_file_id']);
        $this->assertSame('source-1', $result['source_file_id']);
        $this->assertSame('inventory-folder', $result['folder_id']);
        $this->assertSame(['inventory-folder'], $drive->files->copyCalls[0]['file']->parents);
    }

    private function makeService(FakeDriveService $drive): GoogleDriveGlobalService
    {
        $tokens = Mockery::mock(GoogleTokenRepositoryInterface::class);

        return new class($tokens, $drive) extends GoogleDriveGlobalService {
            public function __construct(
                GoogleTokenRepositoryInterface $tokens,
                private readonly FakeDriveService $drive,
            ) {
                parent::__construct($tokens);
            }

            protected function makeAuthorizedDrive()
            {
                return $this->drive;
            }
        };
    }
}

final class FakeDriveService
{
    public FakeDriveFilesResource $files;
    public FakeDrivePermissionsResource $permissions;

    public function __construct()
    {
        $this->files = new FakeDriveFilesResource();
        $this->permissions = new FakeDrivePermissionsResource();
    }
}

final class FakeDriveFilesResource
{
    public ?object $existingFolder = null;
    public ?object $createResult = null;
    public ?object $copyResult = null;
    public array $getResults = [];
    public array $listOptions = [];
    public array $createCalls = [];
    public array $copyCalls = [];
    public array $getCalls = [];

    public function listFiles(array $options): object
    {
        $this->listOptions[] = $options;
        $existingFolder = $this->existingFolder;

        return new class($existingFolder) {
            public function __construct(private readonly ?object $existingFolder) {}

            public function getFiles(): array
            {
                return $this->existingFolder ? [$this->existingFolder] : [];
            }
        };
    }

    public function create(object $file, array $options): object
    {
        $this->createCalls[] = [
            'file' => $file,
            'options' => $options,
        ];

        return $this->createResult ?? (object) [
            'id' => 'created-1',
            'name' => 'created',
            'mimeType' => 'application/octet-stream',
            'size' => 0,
            'webViewLink' => null,
            'webContentLink' => null,
            'createdTime' => null,
            'parents' => ['default-folder'],
        ];
    }

    public function get(string $fileId, array $options): object
    {
        $this->getCalls[] = [
            'file_id' => $fileId,
            'options' => $options,
        ];

        if (! array_key_exists($fileId, $this->getResults)) {
            throw new \RuntimeException("Missing fake file metadata for {$fileId}.");
        }

        return $this->getResults[$fileId];
    }

    public function copy(string $sourceFileId, object $file, array $options): object
    {
        $this->copyCalls[] = [
            'source_file_id' => $sourceFileId,
            'file' => $file,
            'options' => $options,
        ];

        return $this->copyResult ?? (object) [
            'id' => 'copy-1',
            'name' => 'copy',
            'mimeType' => 'application/octet-stream',
            'size' => 0,
            'webViewLink' => null,
            'webContentLink' => null,
            'createdTime' => null,
            'parents' => ['default-folder'],
        ];
    }
}

final class FakeDrivePermissionsResource
{
    public array $createCalls = [];

    public function create(string $fileId, object $permission, array $options): object
    {
        $this->createCalls[] = [
            'file_id' => $fileId,
            'permission' => $permission,
            'options' => $options,
        ];

        return (object) ['id' => 'permission-1'];
    }
}