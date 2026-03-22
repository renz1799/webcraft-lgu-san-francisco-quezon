<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirFileRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirRepository;
use App\Modules\GSO\Services\AirFileService;
use App\Modules\GSO\Support\AirStatuses;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoAirFileServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('gso.storage.air_files_folder_id', 'gso-air-file-root');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_uploads_previews_marks_primary_and_deletes_air_files(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-1',
            'po_number' => 'PO-700',
            'air_number' => 'AIR-2026-7001',
            'status' => AirStatuses::IN_PROGRESS,
            'drive_folder_id' => null,
            'requesting_department_id' => null,
            'fund_source_id' => null,
            'created_by_user_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(3);

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldReceive('ensureFolder')
            ->once()
            ->with('PO-700', 'gso-air-file-root')
            ->andReturn([
                'drive_folder_id' => 'drive-folder-700',
                'name' => 'PO-700',
                'created' => true,
                'parent_id' => 'gso-air-file-root',
            ]);

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldReceive('upload')
            ->twice()
            ->with(Mockery::type(UploadedFile::class), null, false, 'drive-folder-700')
            ->andReturn(
                [
                    'drive_file_id' => 'drive-file-1',
                    'mime_type' => 'image/jpeg',
                    'size' => 12000,
                    'folder_id' => 'drive-folder-700',
                ],
                [
                    'drive_file_id' => 'drive-file-2',
                    'mime_type' => 'application/pdf',
                    'size' => 24000,
                    'folder_id' => 'drive-folder-700',
                ],
            );
        $driveFiles->shouldReceive('download')
            ->once()
            ->with('drive-file-1')
            ->andReturn([
                'name' => 'delivery-photo.jpg',
                'mime_type' => 'image/jpeg',
                'bytes' => 'preview-bytes',
            ]);
        $driveFiles->shouldReceive('deleteFile')
            ->once()
            ->with('drive-file-2');

        $service = new AirFileService(
            new EloquentAirRepository(),
            new EloquentAirFileRepository(),
            $audit,
            $driveFolders,
            $driveFiles,
        );

        $uploaded = $service->upload('actor-1', 'air-1', [
            UploadedFile::fake()->image('delivery-photo.jpg'),
            UploadedFile::fake()->create('delivery-receipt.pdf', 64, 'application/pdf'),
        ]);

        $this->assertSame(2, $uploaded['air']['file_count']);
        $this->assertSame('drive-folder-700', $uploaded['air']['drive_folder_id']);
        $this->assertCount(2, $uploaded['files']);
        $this->assertTrue($uploaded['files'][0]['is_primary']);
        $this->assertStringContainsString('/gso/air/air-1/files/', (string) $uploaded['files'][0]['preview_url']);

        $this->assertDatabaseHas('airs', [
            'id' => 'air-1',
            'drive_folder_id' => 'drive-folder-700',
        ]);
        $this->assertDatabaseHas('air_files', [
            'air_id' => 'air-1',
            'path' => 'drive-file-1',
            'driver' => 'google',
            'type' => 'photo',
            'is_primary' => 1,
        ]);

        $preview = $service->preview('air-1', (string) $uploaded['files'][0]['id']);

        $this->assertSame('delivery-photo.jpg', $preview['name']);
        $this->assertSame('image/jpeg', $preview['mime']);
        $this->assertSame('preview-bytes', $preview['bytes']);

        $primaryPayload = $service->setPrimary(
            'actor-1',
            'air-1',
            (string) $uploaded['files'][1]['id'],
        );

        $this->assertSame((string) $uploaded['files'][1]['id'], $primaryPayload['files'][0]['id']);
        $this->assertTrue($primaryPayload['files'][0]['is_primary']);
        $this->assertSame('PDF', $primaryPayload['files'][0]['type_text']);

        $deletedPayload = $service->delete(
            'actor-1',
            'air-1',
            (string) $uploaded['files'][1]['id'],
        );

        $this->assertSame(1, $deletedPayload['air']['file_count']);
        $this->assertCount(1, $deletedPayload['files']);
        $this->assertTrue($deletedPayload['files'][0]['is_primary']);
        $this->assertDatabaseMissing('air_files', [
            'id' => (string) $uploaded['files'][1]['id'],
            'deleted_at' => null,
        ]);
    }

    public function test_it_requires_po_number_before_uploading_air_files(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-no-po',
            'po_number' => null,
            'air_number' => 'AIR-2026-7002',
            'status' => AirStatuses::DRAFT,
            'drive_folder_id' => null,
            'requesting_department_id' => null,
            'fund_source_id' => null,
            'created_by_user_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldNotReceive('ensureFolder');

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldNotReceive('upload');

        $service = new AirFileService(
            new EloquentAirRepository(),
            new EloquentAirFileRepository(),
            $audit,
            $driveFolders,
            $driveFiles,
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('PO number is required before uploading AIR files.');

        $service->upload('actor-1', 'air-no-po', [
            UploadedFile::fake()->image('delivery-photo.jpg'),
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('airs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_air_id')->nullable();
            $table->unsignedInteger('continuation_no')->nullable();
            $table->string('po_number', 255)->nullable();
            $table->date('po_date')->nullable();
            $table->string('air_number', 255)->nullable();
            $table->date('air_date')->nullable();
            $table->string('invoice_number', 255)->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('supplier_name', 255)->nullable();
            $table->uuid('requesting_department_id')->nullable();
            $table->string('requesting_department_name_snapshot', 255)->nullable();
            $table->string('requesting_department_code_snapshot', 255)->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('fund', 255)->nullable();
            $table->string('status', 50)->default(AirStatuses::DRAFT);
            $table->date('date_received')->nullable();
            $table->boolean('received_completeness')->nullable();
            $table->text('received_notes')->nullable();
            $table->date('date_inspected')->nullable();
            $table->boolean('inspection_verified')->nullable();
            $table->text('inspection_notes')->nullable();
            $table->string('inspected_by_name', 255)->nullable();
            $table->string('accepted_by_name', 255)->nullable();
            $table->string('drive_folder_id', 120)->nullable();
            $table->uuid('created_by_user_id')->nullable();
            $table->string('created_by_name_snapshot', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('air_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_id');
            $table->string('driver', 20)->default('google');
            $table->string('path');
            $table->string('type', 30)->default('photo');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
