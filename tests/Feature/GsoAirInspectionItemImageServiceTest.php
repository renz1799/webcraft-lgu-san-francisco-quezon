<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Core\Services\Contracts\Notifications\NotificationServiceInterface;
use App\Core\Services\Contracts\Notifications\WorkflowNotificationSettingsServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskServiceInterface;
use App\Modules\GSO\Builders\Contracts\Air\AirDatatableRowBuilderInterface;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemUnitRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirRepository;
use App\Modules\GSO\Services\Air\AirInspectionItemImageService;
use App\Modules\GSO\Services\Air\AirInspectionService;
use App\Modules\GSO\Services\Contracts\GsoStorageSettingsServiceInterface;
use App\Modules\GSO\Support\Air\AirStatuses;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoAirInspectionItemImageServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('gso.storage.inspection_photos_folder_id', 'gso-air-item-root');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_uploads_and_deletes_air_item_images_and_exposes_them_in_inspection_payload(): void
    {
        $this->seedBaseAirData(
            airId: 'air-stock-1',
            airItemId: 'air-item-stock-1',
            poNumber: 'PO-700',
            trackingType: 'consumable',
            requiresSerial: false,
            isSemiExpendable: false,
            itemName: 'Bond Paper',
            stockNo: 'STK-100',
        );

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(2);

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldReceive('ensureFolder')
            ->once()
            ->with('PO-700 - Bond Paper (STK-100)', 'gso-air-item-root')
            ->andReturn([
                'drive_folder_id' => 'drive-folder-700',
                'name' => 'PO-700 - Bond Paper (STK-100)',
                'created' => true,
                'parent_id' => 'gso-air-item-root',
            ]);

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldReceive('upload')
            ->once()
            ->with(Mockery::type(UploadedFile::class), null, false, 'drive-folder-700')
            ->andReturn([
                'drive_file_id' => 'drive-file-700',
                'mime_type' => 'image/jpeg',
                'size' => 18000,
                'folder_id' => 'drive-folder-700',
            ]);
        $driveFiles->shouldReceive('deleteFile')
            ->once()
            ->with('drive-file-700');

        $service = new AirInspectionItemImageService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            $audit,
            $driveFolders,
            $driveFiles,
            $this->mockStorageSettings(),
        );

        $service->upload('actor-1', 'air-stock-1', 'air-item-stock-1', [
            UploadedFile::fake()->image('stock-photo.jpg'),
        ]);

        $this->assertDatabaseHas('air_item_images', [
            'air_item_id' => 'air-item-stock-1',
            'storage_provider' => 'google',
            'storage_disk' => 'google_drive',
            'storage_path' => 'drive-file-700',
            'external_file_id' => 'drive-file-700',
            'original_name' => 'stock-photo.jpg',
        ]);

        $inspectionPayload = $this->makeInspectionService()->getForInspection(
            'air-stock-1',
        );
        $this->assertCount(1, $inspectionPayload['items'][0]['images']);
        $this->assertSame(
            'drive-file-700',
            $inspectionPayload['items'][0]['images'][0]['external_file_id'],
        );
        $this->assertStringContainsString(
            '/drive/preview/drive-file-700',
            (string) $inspectionPayload['items'][0]['images'][0]['preview_url'],
        );

        $imageId = DB::table('air_item_images')
            ->where('air_item_id', 'air-item-stock-1')
            ->value('id');

        $service->delete(
            'actor-1',
            'air-stock-1',
            'air-item-stock-1',
            (string) $imageId,
        );

        $updatedPayload = $this->makeInspectionService()->getForInspection(
            'air-stock-1',
        );
        $this->assertCount(0, $updatedPayload['items'][0]['images']);
        $this->assertDatabaseMissing('air_item_images', [
            'id' => (string) $imageId,
            'deleted_at' => null,
        ]);
    }

    public function test_it_requires_po_number_before_uploading_air_item_images(): void
    {
        $this->seedBaseAirData(
            airId: 'air-stock-2',
            airItemId: 'air-item-stock-2',
            poNumber: null,
            trackingType: 'consumable',
            requiresSerial: false,
            isSemiExpendable: false,
            itemName: 'Printer Ink',
            stockNo: 'STK-200',
        );

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldNotReceive('ensureFolder');

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldNotReceive('upload');

        $service = new AirInspectionItemImageService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            $audit,
            $driveFolders,
            $driveFiles,
            $this->mockStorageSettings(),
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'PO number is required before uploading AIR item images.',
        );

        $service->upload('actor-1', 'air-stock-2', 'air-item-stock-2', [
            UploadedFile::fake()->image('stock-photo.jpg'),
        ]);
    }

    public function test_it_rejects_serialized_lines_for_item_image_upload(): void
    {
        $this->seedBaseAirData(
            airId: 'air-serialized-1',
            airItemId: 'air-item-serialized-1',
            poNumber: 'PO-800',
            trackingType: 'property',
            requiresSerial: true,
            isSemiExpendable: false,
            itemName: 'Laptop Computer',
            stockNo: 'ITM-500',
        );

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldNotReceive('ensureFolder');

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldNotReceive('upload');

        $service = new AirInspectionItemImageService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            $audit,
            $driveFolders,
            $driveFiles,
            $this->mockStorageSettings(),
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'This AIR line stores inspection images on inspection units instead of the AIR item record.',
        );

        $service->upload(
            'actor-1',
            'air-serialized-1',
            'air-item-serialized-1',
            [UploadedFile::fake()->image('serialized-photo.jpg')],
        );
    }

    private function makeInspectionService(): AirInspectionService
    {
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $notifications = Mockery::mock(NotificationServiceInterface::class);
        $workflowNotifications = Mockery::mock(
            WorkflowNotificationSettingsServiceInterface::class,
        );
        $airRowBuilder = Mockery::mock(AirDatatableRowBuilderInterface::class);

        $airRowBuilder->shouldReceive('build')->andReturnUsing(
            fn($air) => [
                'id' => (string) $air->id,
                'status' => (string) ($air->status ?? ''),
            ],
        );

        return new AirInspectionService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            $audit,
            $tasks,
            $notifications,
            $workflowNotifications,
            $airRowBuilder,
        );
    }

    private function seedBaseAirData(
        string $airId,
        string $airItemId,
        ?string $poNumber,
        string $trackingType,
        bool $requiresSerial,
        bool $isSemiExpendable,
        string $itemName,
        string $stockNo,
    ): void {
        DB::table('airs')->insert([
            'id' => $airId,
            'po_number' => $poNumber,
            'air_number' => 'AIR-2026-7001',
            'status' => AirStatuses::IN_PROGRESS,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'item-1',
            'asset_id' => null,
            'item_name' => $itemName,
            'description' => 'Inspection item',
            'base_unit' => 'box',
            'item_identification' => $stockNo,
            'tracking_type' => $trackingType,
            'requires_serial' => $requiresSerial,
            'is_semi_expendable' => $isSemiExpendable,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => $airItemId,
            'air_id' => $airId,
            'item_id' => 'item-1',
            'stock_no_snapshot' => $stockNo,
            'item_name_snapshot' => $itemName,
            'description_snapshot' => 'Inspection item',
            'unit_snapshot' => 'box',
            'qty_ordered' => 2,
            'qty_delivered' => 2,
            'qty_accepted' => 2,
            'tracking_type_snapshot' => $trackingType,
            'requires_serial_snapshot' => $requiresSerial,
            'is_semi_expendable_snapshot' => $isSemiExpendable,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
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

        Schema::create('air_item_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id');
            $table->string('storage_provider', 50)->default('google');
            $table->string('storage_disk', 50)->nullable();
            $table->string('storage_path');
            $table->string('external_file_id', 255)->nullable();
            $table->string('original_name')->nullable();
            $table->string('stored_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('air_item_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id');
            $table->uuid('client_request_id')->nullable();
            $table->unsignedInteger('line_no')->default(1);
            $table->string('name', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('component_cost', 15, 2)->default(0);
            $table->string('serial_number', 255)->nullable();
            $table->string('condition', 255)->nullable();
            $table->boolean('is_present')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function mockStorageSettings(): GsoStorageSettingsServiceInterface
    {
        $storage = Mockery::mock(GsoStorageSettingsServiceInterface::class);
        $storage->shouldReceive('inspectionPhotosFolderId')->andReturn(
            'gso-air-item-root',
        );
        $storage->shouldReceive('airUnitFilesFolderId')->andReturn(
            'gso-air-unit-root',
        );

        return $storage;
    }
}
