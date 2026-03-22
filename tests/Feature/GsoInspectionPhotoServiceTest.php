<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Repositories\Eloquent\EloquentInspectionPhotoRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInspectionRepository;
use App\Modules\GSO\Services\InspectionPhotoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoInspectionPhotoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('gso.storage.inspection_photos_folder_id', 'gso-drive-root');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_uploads_lists_and_deletes_inspection_photos(): void
    {
        DB::table('users')->insert([
            'id' => 'actor-1',
            'username' => 'inspector1',
            'email' => 'inspector1@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('inspections')->insert([
            'id' => 'inspection-1',
            'inspector_user_id' => 'actor-1',
            'reviewer_user_id' => null,
            'status' => 'submitted',
            'department_id' => null,
            'item_id' => null,
            'office_department' => 'GSO - General Services Office',
            'accountable_officer' => null,
            'dv_number' => null,
            'po_number' => 'PO-001',
            'observed_description' => 'Uploaded for evidence',
            'item_name' => 'Laptop Computer',
            'brand' => null,
            'model' => null,
            'serial_number' => null,
            'acquisition_cost' => null,
            'acquisition_date' => null,
            'quantity' => 1,
            'condition' => 'good',
            'drive_folder_id' => null,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->twice();

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldReceive('ensureFolder')
            ->once()
            ->with('PO-001', 'gso-drive-root')
            ->andReturn([
                'drive_folder_id' => 'drive-folder-1',
                'name' => 'PO-001',
                'created' => true,
                'parent_id' => 'gso-drive-root',
            ]);

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldReceive('upload')
            ->twice()
            ->with(Mockery::type(UploadedFile::class), null, false, 'drive-folder-1')
            ->andReturn(
                [
                    'drive_file_id' => 'drive-file-1',
                    'mime_type' => 'image/jpeg',
                    'size' => 12000,
                    'folder_id' => 'drive-folder-1',
                ],
                [
                    'drive_file_id' => 'drive-file-2',
                    'mime_type' => 'image/png',
                    'size' => 14000,
                    'folder_id' => 'drive-folder-1',
                ],
            );
        $driveFiles->shouldReceive('deleteFile')
            ->once()
            ->with(Mockery::on(fn (mixed $value): bool => in_array($value, ['drive-file-1', 'drive-file-2'], true)));

        $service = new InspectionPhotoService(
            new EloquentInspectionRepository(),
            new EloquentInspectionPhotoRepository(),
            $audit,
            $driveFolders,
            $driveFiles,
        );

        $uploaded = $service->upload('actor-1', 'inspection-1', [
            UploadedFile::fake()->image('inspection-1.jpg'),
            UploadedFile::fake()->image('inspection-2.png'),
        ]);

        $this->assertSame(2, $uploaded['inspection']['photo_count']);
        $this->assertSame('drive-folder-1', $uploaded['inspection']['drive_folder_id']);
        $this->assertCount(2, $uploaded['photos']);
        $this->assertStringContainsString('/drive/preview/', (string) $uploaded['photos'][0]['preview_url']);

        $this->assertDatabaseHas('inspection_photos', [
            'inspection_id' => 'inspection-1',
            'path' => 'drive-file-1',
            'driver' => 'google',
        ]);

        $payload = $service->listForInspection('inspection-1');
        $this->assertSame(2, $payload['inspection']['photo_count']);

        $deletePayload = $service->delete('actor-1', 'inspection-1', (string) $uploaded['photos'][0]['id']);

        $this->assertSame(1, $deletePayload['inspection']['photo_count']);
        $this->assertDatabaseMissing('inspection_photos', [
            'id' => (string) $uploaded['photos'][0]['id'],
            'deleted_at' => null,
        ]);
    }

    public function test_it_requires_po_number_before_uploading_photos(): void
    {
        DB::table('users')->insert([
            'id' => 'actor-1',
            'username' => 'inspector1',
            'email' => 'inspector1@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('inspections')->insert([
            'id' => 'inspection-no-po',
            'inspector_user_id' => 'actor-1',
            'reviewer_user_id' => null,
            'status' => 'draft',
            'department_id' => null,
            'item_id' => null,
            'office_department' => null,
            'accountable_officer' => null,
            'dv_number' => null,
            'po_number' => null,
            'observed_description' => null,
            'item_name' => 'Office Chair',
            'brand' => null,
            'model' => null,
            'serial_number' => null,
            'acquisition_cost' => null,
            'acquisition_date' => null,
            'quantity' => 1,
            'condition' => 'good',
            'drive_folder_id' => null,
            'remarks' => null,
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

        $service = new InspectionPhotoService(
            new EloquentInspectionRepository(),
            new EloquentInspectionPhotoRepository(),
            $audit,
            $driveFolders,
            $driveFiles,
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('PO number is required before uploading inspection photos.');

        $service->upload('actor-1', 'inspection-no-po', [
            UploadedFile::fake()->image('inspection.jpg'),
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

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_name', 255)->nullable();
            $table->string('item_identification', 255)->nullable();
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

        Schema::create('inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inspector_user_id');
            $table->uuid('reviewer_user_id')->nullable();
            $table->string('status', 50)->default('draft');
            $table->uuid('department_id')->nullable();
            $table->uuid('item_id')->nullable();
            $table->string('office_department', 255)->nullable();
            $table->string('accountable_officer', 255)->nullable();
            $table->string('dv_number', 120)->nullable();
            $table->string('po_number', 120)->nullable();
            $table->text('observed_description')->nullable();
            $table->string('item_name', 255)->nullable();
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->date('acquisition_date')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition', 100)->default('good');
            $table->string('drive_folder_id', 120)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inspection_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inspection_id');
            $table->string('driver', 20)->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
