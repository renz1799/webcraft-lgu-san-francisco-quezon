<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Models\InventoryItemEvent;
use App\Modules\GSO\Repositories\Contracts\InspectionRepositoryInterface;
use App\Modules\GSO\Repositories\Eloquent\EloquentInspectionRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemFileRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemRepository;
use App\Modules\GSO\Services\Contracts\GsoStorageSettingsServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Services\InventoryItemFileService;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class GsoInventoryItemFileServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('gso.storage.inventory_files_folder_id', 'gso-inventory-root');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_uploads_lists_and_deletes_inventory_files(): void
    {
        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Laptop Computer',
            'item_identification' => 'LT-001',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('inventory_items')->insert([
            'id' => 'inventory-1',
            'item_id' => 'item-1',
            'department_id' => null,
            'fund_source_id' => null,
            'property_number' => 'PROP-001',
            'acquisition_date' => '2026-03-01',
            'acquisition_cost' => 55000.25,
            'description' => 'Primary deployment unit',
            'quantity' => 1,
            'unit' => 'unit',
            'stock_number' => 'STK-001',
            'service_life' => 5,
            'is_ics' => false,
            'accountable_officer' => null,
            'accountable_officer_id' => null,
            'custody_state' => 'pool',
            'status' => InventoryStatuses::SERVICEABLE,
            'condition' => InventoryConditions::GOOD,
            'brand' => 'Lenovo',
            'model' => 'ThinkPad',
            'serial_number' => 'SN-001',
            'po_number' => 'PO-001',
            'drive_folder_id' => null,
            'remarks' => 'Ready for upload',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $inspectionRepository = Mockery::mock(InspectionRepositoryInterface::class);
        $inspectionRepository->shouldNotReceive('findOrFail');

        $events = Mockery::mock(InventoryItemEventServiceInterface::class);
        $events->shouldNotReceive('create');

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->twice();

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldReceive('ensureFolder')
            ->once()
            ->with('PROP-001', 'gso-inventory-root')
            ->andReturn([
                'drive_folder_id' => 'drive-folder-1',
                'name' => 'PROP-001',
                'created' => true,
                'parent_id' => 'gso-inventory-root',
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
                    'mime_type' => 'application/pdf',
                    'size' => 22000,
                    'folder_id' => 'drive-folder-1',
                ],
            );
        $driveFiles->shouldReceive('deleteFile')
            ->once()
            ->with(Mockery::on(fn (mixed $value): bool => in_array($value, ['drive-file-1', 'drive-file-2'], true)));

        $service = new InventoryItemFileService(
            new EloquentInventoryItemRepository(),
            new EloquentInventoryItemFileRepository(),
            $inspectionRepository,
            $events,
            $audit,
            $driveFolders,
            $driveFiles,
            $this->mockStorageSettings(),
        );

        $uploaded = $service->upload('actor-1', 'inventory-1', [
            UploadedFile::fake()->image('inventory-front.jpg'),
            UploadedFile::fake()->create('property-card.pdf', 128, 'application/pdf'),
        ]);

        $this->assertSame(2, $uploaded['inventory_item']['file_count']);
        $this->assertSame('drive-folder-1', $uploaded['inventory_item']['drive_folder_id']);
        $this->assertCount(2, $uploaded['files']);
        $this->assertSame('Photo', $uploaded['files'][0]['type_text']);

        $this->assertDatabaseHas('inventory_item_files', [
            'inventory_item_id' => 'inventory-1',
            'path' => 'drive-file-1',
            'driver' => 'google',
            'type' => 'photo',
        ]);

        $listPayload = $service->listForInventoryItem('inventory-1');
        $this->assertSame(2, $listPayload['inventory_item']['file_count']);

        $deletePayload = $service->delete('actor-1', 'inventory-1', (string) $uploaded['files'][0]['id']);

        $this->assertSame(1, $deletePayload['inventory_item']['file_count']);
        $this->assertDatabaseMissing('inventory_item_files', [
            'id' => (string) $uploaded['files'][0]['id'],
            'deleted_at' => null,
        ]);
    }

    public function test_it_imports_google_inspection_photos_into_inventory_history(): void
    {
        DB::table('users')->insert([
            'id' => 'actor-1',
            'username' => 'inspector1',
            'email' => 'inspector1@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Laptop Computer',
            'item_identification' => 'LT-001',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('departments')->insert([
            'id' => 'dept-1',
            'code' => 'GSO',
            'name' => 'General Services Office',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('inventory_items')->insert([
            'id' => 'inventory-1',
            'item_id' => 'item-1',
            'department_id' => 'dept-1',
            'fund_source_id' => null,
            'property_number' => 'PROP-100',
            'acquisition_date' => '2026-03-01',
            'acquisition_cost' => 55000.25,
            'description' => 'Inspection bridge target',
            'quantity' => 1,
            'unit' => 'unit',
            'stock_number' => 'STK-100',
            'service_life' => 5,
            'is_ics' => false,
            'accountable_officer' => 'Maria Clara',
            'accountable_officer_id' => null,
            'custody_state' => 'pool',
            'status' => InventoryStatuses::SERVICEABLE,
            'condition' => InventoryConditions::GOOD,
            'brand' => 'Lenovo',
            'model' => 'ThinkPad',
            'serial_number' => 'SN-100',
            'po_number' => null,
            'drive_folder_id' => null,
            'remarks' => 'Awaiting inspection evidence',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('inspections')->insert([
            'id' => 'inspection-1',
            'inspector_user_id' => 'actor-1',
            'reviewer_user_id' => null,
            'status' => 'submitted',
            'department_id' => 'dept-1',
            'item_id' => 'item-1',
            'office_department' => 'GSO - General Services Office',
            'accountable_officer' => 'Maria Clara',
            'dv_number' => 'DV-001',
            'po_number' => 'PO-200',
            'observed_description' => 'Inspection evidence ready',
            'item_name' => 'Laptop Computer',
            'brand' => 'Lenovo',
            'model' => 'ThinkPad',
            'serial_number' => 'SN-100',
            'acquisition_cost' => 55000.25,
            'acquisition_date' => '2026-03-01',
            'quantity' => 1,
            'condition' => InventoryConditions::FAIR,
            'drive_folder_id' => 'inspection-drive-folder',
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('inspection_photos')->insert([
            [
                'id' => 'inspection-photo-1',
                'inspection_id' => 'inspection-1',
                'driver' => 'google',
                'path' => 'source-drive-photo-1',
                'original_name' => 'front-view.jpg',
                'mime' => 'image/jpeg',
                'size' => 12000,
                'caption' => 'Front view',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'inspection-photo-2',
                'inspection_id' => 'inspection-1',
                'driver' => 'public',
                'path' => 'local-photo-2',
                'original_name' => 'local-copy.jpg',
                'mime' => 'image/jpeg',
                'size' => 8000,
                'caption' => 'Local copy',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $events = Mockery::mock(InventoryItemEventServiceInterface::class);
        $events->shouldReceive('create')
            ->once()
            ->with('actor-1', 'inventory-1', Mockery::on(function (array $payload): bool {
                return ($payload['event_type'] ?? null) === InventoryEventTypes::CREATED_FROM_INSPECTION
                    && ($payload['reference_type'] ?? null) === 'Inspection'
                    && ($payload['reference_id'] ?? null) === 'inspection-1'
                    && ($payload['reference_no'] ?? null) === 'PO PO-200 / DV DV-001'
                    && ($payload['notes'] ?? null) === 'Inspection evidence copied into inventory file history.';
            }))
            ->andReturn(new InventoryItemEvent([
                'id' => 'inventory-event-1',
                'inventory_item_id' => 'inventory-1',
                'event_type' => InventoryEventTypes::CREATED_FROM_INSPECTION,
            ]));

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->once();

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldReceive('ensureFolder')
            ->once()
            ->with('PROP-100', 'gso-inventory-root')
            ->andReturn([
                'drive_folder_id' => 'drive-folder-import',
                'name' => 'PROP-100',
                'created' => true,
                'parent_id' => 'gso-inventory-root',
            ]);

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldReceive('copyFile')
            ->once()
            ->with('source-drive-photo-1', 'front-view.jpg', 'drive-folder-import')
            ->andReturn([
                'drive_file_id' => 'copied-drive-photo-1',
                'mime_type' => 'image/jpeg',
                'size' => 12000,
            ]);

        $service = new InventoryItemFileService(
            new EloquentInventoryItemRepository(),
            new EloquentInventoryItemFileRepository(),
            new EloquentInspectionRepository(),
            $events,
            $audit,
            $driveFolders,
            $driveFiles,
            $this->mockStorageSettings(),
        );

        $payload = $service->importInspectionPhotos('actor-1', 'inventory-1', 'inspection-1');

        $this->assertSame(1, $payload['inventory_item']['file_count']);
        $this->assertSame('drive-folder-import', $payload['inventory_item']['drive_folder_id']);
        $this->assertCount(1, $payload['files']);
        $this->assertSame('photo', $payload['files'][0]['type']);

        $this->assertDatabaseHas('inventory_item_files', [
            'inventory_item_id' => 'inventory-1',
            'path' => 'copied-drive-photo-1',
            'driver' => 'google',
            'type' => 'photo',
            'caption' => 'Front view',
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
            $table->string('item_name', 255);
            $table->string('item_identification', 255)->nullable();
            $table->string('tracking_type', 50)->default('property');
            $table->boolean('requires_serial')->default(false);
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
            $table->string('name', 150);
            $table->string('code', 30)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('air_item_unit_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('property_number', 120)->nullable();
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->string('stock_number', 120)->nullable();
            $table->unsignedInteger('service_life')->nullable();
            $table->boolean('is_ics')->default(false);
            $table->string('accountable_officer', 255)->nullable();
            $table->uuid('accountable_officer_id')->nullable();
            $table->string('custody_state', 20)->default('pool');
            $table->string('status', 100)->nullable();
            $table->string('condition', 100)->nullable();
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->string('po_number', 120)->nullable();
            $table->string('drive_folder_id', 120)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_item_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
            $table->string('driver', 20)->default('public');
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

        Schema::create('inventory_item_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
            $table->uuid('department_id')->nullable();
            $table->uuid('performed_by_user_id')->nullable();
            $table->string('event_type', 100);
            $table->dateTime('event_date')->nullable();
            $table->unsignedInteger('qty_in')->default(0);
            $table->unsignedInteger('qty_out')->default(0);
            $table->decimal('amount_snapshot', 15, 2)->nullable();
            $table->string('unit_snapshot', 50)->nullable();
            $table->string('office_snapshot', 255)->nullable();
            $table->string('officer_snapshot', 255)->nullable();
            $table->string('status', 100)->nullable();
            $table->string('condition', 100)->nullable();
            $table->string('person_accountable', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->string('reference_no', 120)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->uuid('fund_source_id')->nullable();
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

    private function mockStorageSettings(): GsoStorageSettingsServiceInterface
    {
        $storageSettings = Mockery::mock(GsoStorageSettingsServiceInterface::class);
        $storageSettings->shouldReceive('inventoryFilesFolderId')
            ->andReturn('gso-inventory-root');

        return $storageSettings;
    }
}
