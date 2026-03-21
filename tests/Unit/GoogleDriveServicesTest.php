<?php

namespace Tests\Unit;

use App\Core\Builders\GoogleDrive\GoogleDriveFileMetadataBuilder;
use App\Core\Builders\GoogleDrive\GoogleDriveFolderNameSanitizer;
use App\Core\Models\GoogleToken;
use App\Core\Repositories\Contracts\GoogleTokenRepositoryInterface;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveClientFactoryInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveSettingsProviderInterface;
use App\Core\Services\GoogleDrive\GoogleDriveConnectionService;
use App\Core\Services\GoogleDrive\GoogleDriveFileService;
use App\Core\Services\GoogleDrive\GoogleDriveFolderService;
use App\Core\Support\CurrentContext;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Mockery;
use Tests\TestCase;

class GoogleDriveServicesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_connection_service_uses_current_context_and_preserves_existing_refresh_token(): void
    {
        $tokens = Mockery::mock(GoogleTokenRepositoryInterface::class);
        $clientFactory = Mockery::mock(GoogleDriveClientFactoryInterface::class);
        $context = Mockery::mock(CurrentContext::class);
        $moduleDepartments = Mockery::mock(ModuleDepartmentResolverInterface::class);
        $client = Mockery::mock(GoogleClient::class);

        $context->shouldReceive('moduleId')->once()->andReturn('module-1');
        $moduleDepartments->shouldReceive('resolveForModule')->once()->with('module-1')->andReturn('department-1');

        $clientFactory->shouldReceive('makeClient')
            ->once()
            ->andReturn($client);

        $client->shouldReceive('fetchAccessTokenWithAuthCode')
            ->once()
            ->with('oauth-code')
            ->andReturn([
                'access_token' => 'new-access-token',
                'expires_in' => 3600,
            ]);

        $tokens->shouldReceive('findForContext')
            ->once()
            ->with('module-1', 'department-1')
            ->andReturn(new GoogleToken([
                'refresh_token' => Crypt::encryptString('persisted-refresh-token'),
            ]));

        $tokens->shouldReceive('upsertForContext')
            ->once()
            ->with(
                'module-1',
                'department-1',
                Mockery::on(function (array $payload): bool {
                    $this->assertSame('user-1', $payload['connected_by_user_id']);
                    $this->assertSame('new-access-token', $payload['access_token']);
                    $this->assertSame('persisted-refresh-token', $payload['refresh_token']);
                    $this->assertNotNull($payload['expires_at']);

                    return true;
                })
            );

        $service = new GoogleDriveConnectionService($tokens, $clientFactory, $context, $moduleDepartments);

        $service->handleCallback('oauth-code', 'user-1');
    }

    public function test_folder_service_sanitizes_names_and_reuses_existing_child_folder(): void
    {
        $drive = new FakeDriveService();
        $drive->files->existingFolder = (object) [
            'id' => 'folder-1',
            'name' => 'PO-2026-01',
            'parents' => ['root-folder'],
        ];

        $factory = Mockery::mock(GoogleDriveClientFactoryInterface::class);
        $factory->shouldReceive('makeAuthorizedDrive')
            ->once()
            ->andReturn($drive);

        $service = new GoogleDriveFolderService($factory, new GoogleDriveFolderNameSanitizer());

        $this->assertSame('PO-2026-01 Main Office', $service->sanitizeFolderName(" PO/2026:01   Main Office "));

        $result = $service->ensureFolder('PO/2026:01', 'root-folder');

        $this->assertSame('folder-1', $result['drive_folder_id']);
        $this->assertSame('PO-2026-01', $result['name']);
        $this->assertFalse($result['created']);
        $this->assertCount(0, $drive->files->createCalls);
        $this->assertStringContainsString("'root-folder' in parents", $drive->files->listOptions[0]['q']);
        $this->assertStringContainsString("name = 'PO-2026-01'", $drive->files->listOptions[0]['q']);
    }

    public function test_file_service_upload_and_copy_delegate_to_drive_and_build_metadata(): void
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

        $factory = Mockery::mock(GoogleDriveClientFactoryInterface::class);
        $factory->shouldReceive('makeAuthorizedDrive')
            ->times(3)
            ->andReturn($drive);

        $settings = Mockery::mock(GoogleDriveSettingsProviderInterface::class);
        $settings->shouldReceive('defaultFolderId')->once()->andReturn('target-folder');
        $settings->shouldReceive('defaultMakePublic')->never();

        $service = new GoogleDriveFileService(
            $factory,
            $settings,
            new GoogleDriveFileMetadataBuilder(),
        );

        $file = UploadedFile::fake()->create('photo.jpg', 12, 'image/jpeg');

        $upload = $service->upload($file, null, true, null);

        $this->assertSame('file-1', $upload['drive_file_id']);
        $this->assertSame('target-folder', $upload['folder_id']);
        $this->assertTrue($upload['is_public']);
        $this->assertCount(1, $drive->permissions->createCalls);
        $this->assertSame(['target-folder'], $drive->files->createCalls[0]['file']->parents);

        $copy = $service->copyFile('source-1', 'inventory-photo.jpg', 'inventory-folder');

        $this->assertSame('copy-1', $copy['drive_file_id']);
        $this->assertSame('source-1', $copy['source_file_id']);
        $this->assertSame('inventory-folder', $copy['folder_id']);
        $this->assertSame(['inventory-folder'], $drive->files->copyCalls[0]['file']->parents);

        $service->deleteFile('drive-file-1');

        $this->assertCount(1, $drive->files->deleteCalls);
        $this->assertSame('drive-file-1', $drive->files->deleteCalls[0]['file_id']);
        $this->assertTrue($drive->files->deleteCalls[0]['options']['supportsAllDrives']);
    }
}

final class FakeDriveService extends GoogleDrive
{
    public $files;
    public $permissions;

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
    public array $deleteCalls = [];

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

        if ($options['alt'] ?? null) {
            return new class {
                public function getBody(): string
                {
                    return 'file-bytes';
                }
            };
        }

        if (! array_key_exists($fileId, $this->getResults)) {
            throw new \RuntimeException("Missing fake file metadata for {$fileId}.");
        }

        return $this->getResults[$fileId];
    }

    public function delete(string $fileId, array $options): void
    {
        $this->deleteCalls[] = [
            'file_id' => $fileId,
            'options' => $options,
        ];
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
