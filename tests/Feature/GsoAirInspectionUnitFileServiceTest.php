<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemUnitFileRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemUnitRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirRepository;
use App\Modules\GSO\Services\Air\AirInspectionUnitFileService;
use App\Modules\GSO\Support\Air\AirStatuses;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoAirInspectionUnitFileServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('gso.storage.air_unit_files_folder_id', 'gso-air-unit-root');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_uploads_previews_marks_primary_and_deletes_air_unit_images(): void
    {
        $this->seedBaseAirData('air-1', 'air-item-1', 'unit-1', 'PO-500');

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(3);

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldReceive('ensureFolder')
            ->once()
            ->with('PO-500 - Laptop Computer (ITM-500) - SN-500-A', 'gso-air-unit-root')
            ->andReturn([
                'drive_folder_id' => 'drive-folder-500',
                'name' => 'PO-500 - Laptop Computer (ITM-500) - SN-500-A',
                'created' => true,
                'parent_id' => 'gso-air-unit-root',
            ]);

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldReceive('upload')
            ->twice()
            ->with(Mockery::type(UploadedFile::class), null, false, 'drive-folder-500')
            ->andReturn(
                [
                    'drive_file_id' => 'drive-file-1',
                    'mime_type' => 'image/jpeg',
                    'size' => 12000,
                    'folder_id' => 'drive-folder-500',
                ],
                [
                    'drive_file_id' => 'drive-file-2',
                    'mime_type' => 'image/png',
                    'size' => 32000,
                    'folder_id' => 'drive-folder-500',
                ],
            );
        $driveFiles->shouldReceive('download')
            ->once()
            ->with('drive-file-1')
            ->andReturn([
                'name' => 'unit-photo.jpg',
                'mime_type' => 'image/jpeg',
                'bytes' => 'preview-bytes',
            ]);
        $driveFiles->shouldReceive('deleteFile')
            ->once()
            ->with('drive-file-2');

        $service = new AirInspectionUnitFileService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            new EloquentAirItemUnitFileRepository(),
            $audit,
            $driveFolders,
            $driveFiles,
        );

        $uploaded = $service->upload('actor-1', 'air-1', 'air-item-1', 'unit-1', [
            UploadedFile::fake()->image('unit-photo.jpg'),
            UploadedFile::fake()->image('serial-closeup.png'),
        ], 'serial_photo', 'Serial close-up');

        $this->assertSame(2, $uploaded['unit']['file_count']);
        $this->assertSame('drive-folder-500', $uploaded['unit']['drive_folder_id']);
        $this->assertCount(2, $uploaded['files']);
        $this->assertTrue($uploaded['files'][0]['is_primary']);
        $this->assertStringContainsString(
            '/gso/air/air-1/inspection/items/air-item-1/units/unit-1/files/',
            (string) $uploaded['files'][0]['preview_url'],
        );

        $this->assertDatabaseHas('air_item_units', [
            'id' => 'unit-1',
            'drive_folder_id' => 'drive-folder-500',
        ]);
        $this->assertDatabaseHas('air_item_unit_files', [
            'air_item_unit_id' => 'unit-1',
            'path' => 'drive-file-1',
            'driver' => 'google',
            'type' => 'serial_photo',
            'is_primary' => 1,
            'caption' => 'Serial close-up',
        ]);
        $this->assertSame('Serial close-up', $uploaded['files'][0]['caption']);

        $preview = $service->preview(
            'air-1',
            'air-item-1',
            'unit-1',
            (string) $uploaded['files'][0]['id'],
        );

        $this->assertSame('unit-photo.jpg', $preview['name']);
        $this->assertSame('image/jpeg', $preview['mime']);
        $this->assertSame('preview-bytes', $preview['bytes']);

        $primaryPayload = $service->setPrimary(
            'actor-1',
            'air-1',
            'air-item-1',
            'unit-1',
            (string) $uploaded['files'][1]['id'],
        );

        $this->assertSame((string) $uploaded['files'][1]['id'], $primaryPayload['files'][0]['id']);
        $this->assertTrue($primaryPayload['files'][0]['is_primary']);
        $this->assertSame('Serial Photo', $primaryPayload['files'][0]['type_text']);

        $deletedPayload = $service->delete(
            'actor-1',
            'air-1',
            'air-item-1',
            'unit-1',
            (string) $uploaded['files'][1]['id'],
        );

        $this->assertSame(1, $deletedPayload['unit']['file_count']);
        $this->assertCount(1, $deletedPayload['files']);
        $this->assertTrue($deletedPayload['files'][0]['is_primary']);
        $this->assertDatabaseMissing('air_item_unit_files', [
            'id' => (string) $uploaded['files'][1]['id'],
            'deleted_at' => null,
        ]);
    }

    public function test_it_requires_po_number_before_uploading_air_unit_images(): void
    {
        $this->seedBaseAirData('air-2', 'air-item-2', 'unit-2', null);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldNotReceive('ensureFolder');

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldNotReceive('upload');

        $service = new AirInspectionUnitFileService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            new EloquentAirItemUnitFileRepository(),
            $audit,
            $driveFolders,
            $driveFiles,
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('PO number is required before uploading AIR unit images.');

        $service->upload('actor-1', 'air-2', 'air-item-2', 'unit-2', [
            UploadedFile::fake()->image('unit-photo.jpg'),
        ]);
    }

    public function test_it_rejects_non_image_air_unit_uploads(): void
    {
        $this->seedBaseAirData('air-3', 'air-item-3', 'unit-3', 'PO-900');

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldReceive('ensureFolder')
            ->once()
            ->with('PO-900 - Laptop Computer (ITM-500) - SN-500-A', 'gso-air-unit-root')
            ->andReturn([
                'drive_folder_id' => 'drive-folder-900',
                'name' => 'PO-900 - Laptop Computer (ITM-500) - SN-500-A',
                'created' => true,
                'parent_id' => 'gso-air-unit-root',
            ]);

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldNotReceive('upload');

        $service = new AirInspectionUnitFileService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            new EloquentAirItemUnitFileRepository(),
            $audit,
            $driveFolders,
            $driveFiles,
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Only image uploads are supported for AIR inspection units.');

        $service->upload('actor-1', 'air-3', 'air-item-3', 'unit-3', [
            UploadedFile::fake()->create('inspection-report.pdf', 64, 'application/pdf'),
        ]);
    }

    private function seedBaseAirData(string $airId, string $airItemId, string $unitId, ?string $poNumber): void
    {
        DB::table('airs')->insert([
            'id' => $airId,
            'po_number' => $poNumber,
            'air_number' => 'AIR-2026-5001',
            'status' => AirStatuses::IN_PROGRESS,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'item-1',
            'asset_id' => null,
            'item_name' => 'Laptop Computer',
            'description' => 'Portable computer',
            'base_unit' => 'unit',
            'item_identification' => 'ITM-500',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'is_semi_expendable' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => $airItemId,
            'air_id' => $airId,
            'item_id' => 'item-1',
            'stock_no_snapshot' => 'ITM-500',
            'item_name_snapshot' => 'Laptop Computer',
            'description_snapshot' => 'Portable computer',
            'unit_snapshot' => 'unit',
            'qty_ordered' => 1,
            'qty_delivered' => 1,
            'qty_accepted' => 1,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => true,
            'is_semi_expendable_snapshot' => false,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('air_item_units')->insert([
            'id' => $unitId,
            'air_item_id' => $airItemId,
            'inventory_item_id' => null,
            'brand' => 'Lenovo',
            'model' => 'ThinkPad',
            'serial_number' => $unitId === 'unit-2' ? 'SN-500-B' : 'SN-500-A',
            'property_number' => null,
            'condition_status' => 'good',
            'condition_notes' => null,
            'drive_folder_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_id')->nullable();
            $table->string('item_name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('base_unit', 50)->nullable();
            $table->string('item_identification', 255)->nullable();
            $table->string('tracking_type', 50)->nullable();
            $table->boolean('requires_serial')->default(false);
            $table->boolean('is_semi_expendable')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('item_unit_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->string('from_unit', 50);
            $table->unsignedInteger('multiplier')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('item_component_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->unsignedInteger('line_no')->default(1);
            $table->string('name', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('component_cost', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('airs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('po_number', 255)->nullable();
            $table->string('air_number', 255)->nullable();
            $table->string('status', 50)->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('air_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_id');
            $table->uuid('item_id');
            $table->string('stock_no_snapshot', 255)->nullable();
            $table->string('item_name_snapshot', 255)->nullable();
            $table->text('description_snapshot')->nullable();
            $table->string('unit_snapshot', 50)->nullable();
            $table->unsignedInteger('qty_ordered')->nullable();
            $table->unsignedInteger('qty_delivered')->nullable();
            $table->unsignedInteger('qty_accepted')->nullable();
            $table->string('tracking_type_snapshot', 50)->nullable();
            $table->boolean('requires_serial_snapshot')->default(false);
            $table->boolean('is_semi_expendable_snapshot')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('air_item_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id');
            $table->uuid('inventory_item_id')->nullable();
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->string('property_number', 255)->nullable();
            $table->string('condition_status', 100)->nullable();
            $table->text('condition_notes')->nullable();
            $table->string('drive_folder_id', 120)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('air_item_unit_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_unit_id');
            $table->unsignedInteger('line_no')->default(1);
            $table->string('name', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('component_cost', 15, 2)->default(0);
            $table->string('serial_number', 255)->nullable();
            $table->string('condition', 100)->nullable();
            $table->boolean('is_present')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('air_item_unit_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_unit_id');
            $table->string('driver', 20)->default('google');
            $table->string('path')->nullable();
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
