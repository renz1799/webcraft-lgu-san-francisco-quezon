<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Models\InventoryItemEvent;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemFileRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentStockMovementRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentStockRepository;
use App\Modules\GSO\Services\AssetComponentService;
use App\Modules\GSO\Services\AirInventoryPromotionService;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Support\AirStatuses;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoAirInventoryPromotionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('gso.storage.inventory_files_folder_id', 'gso-inventory-root');
        config()->set('gso.pool.department_id', 'dept-gso');
        config()->set('gso.pool.department_code', 'GSO');
        config()->set('gso.pool.accountable_officer_name', 'GSO Pool');
        config()->set('gso.inventory.ics_unit_cost_threshold', 50000);

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_promotes_property_units_and_consumables_from_an_inspected_air(): void
    {
        $this->seedPromotionData();

        $events = Mockery::mock(InventoryItemEventServiceInterface::class);
        $events->shouldReceive('create')
            ->once()
            ->with(
                'actor-1',
                Mockery::type('string'),
                Mockery::on(function (array $payload): bool {
                    return ($payload['event_type'] ?? null) === 'acquired'
                        && ($payload['reference_type'] ?? null) === 'AIR'
                        && ($payload['reference_id'] ?? null) === 'air-1'
                        && ($payload['fund_source_id'] ?? null) === 'fund-1'
                        && ($payload['status'] ?? null) === InventoryStatuses::SERVICEABLE
                        && ($payload['condition'] ?? null) === InventoryConditions::GOOD;
                })
            )
            ->andReturn(new InventoryItemEvent([
                'id' => 'inventory-event-1',
                'inventory_item_id' => 'inventory-generated',
                'event_type' => 'acquired',
            ]));

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->once();

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldReceive('ensureFolder')
            ->once()
            ->with('PO-2026-001', 'gso-inventory-root')
            ->andReturn([
                'drive_folder_id' => 'inventory-drive-folder-1',
                'name' => 'PO-2026-001',
                'created' => true,
                'parent_id' => 'gso-inventory-root',
            ]);

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldReceive('copyFile')
            ->once()
            ->with('drive-air-unit-file-1', 'front-view.jpg', 'inventory-drive-folder-1')
            ->andReturn([
                'drive_file_id' => 'copied-drive-file-1',
                'mime_type' => 'image/jpeg',
                'size' => 14000,
            ]);

        $service = new AirInventoryPromotionService(
            new EloquentInventoryItemRepository(),
            new EloquentInventoryItemFileRepository(),
            $events,
            new EloquentStockRepository(),
            new EloquentStockMovementRepository(),
            new AssetComponentService(),
            $audit,
            $driveFolders,
            $driveFiles,
        );

        $eligibility = $service->getEligibility('air-1');

        $this->assertSame(1, $eligibility['summary']['property_units_count']);
        $this->assertSame(0, $eligibility['summary']['blocked_property_units_count']);
        $this->assertSame(1, $eligibility['summary']['consumable_lines_count']);
        $this->assertSame(2, $eligibility['summary']['consumable_qty_accepted']);
        $this->assertSame(20, $eligibility['consumables'][0]['base_qty']);
        $this->assertTrue((bool) ($eligibility['property_units'][0]['has_components'] ?? false));
        $this->assertSame(1, (int) ($eligibility['property_units'][0]['component_count'] ?? 0));
        $this->assertSame(75000.0, (float) ($eligibility['property_units'][0]['component_total_cost'] ?? 0));
        $this->assertTrue((bool) ($eligibility['property_units'][0]['components_complete'] ?? false));
        $this->assertNull($eligibility['property_units'][0]['promotion_blocked_reason'] ?? null);
        $this->assertNull($eligibility['property_units'][0]['promotion_warning'] ?? null);

        $result = $service->promote(
            actorUserId: 'actor-1',
            airId: 'air-1',
            airItemUnitIds: ['air-unit-1'],
            actorName: 'gsoadmin',
        );

        $this->assertSame(1, $result['property_created']);
        $this->assertSame(0, $result['property_skipped']);
        $this->assertSame(1, $result['consumable_posted']);
        $this->assertSame(0, $result['consumable_skipped']);
        $this->assertSame(1, $result['copied_files']);
        $this->assertSame(1, $result['components_copied']);
        $this->assertCount(1, $result['inventory_item_ids']);

        $inventoryItemId = $result['inventory_item_ids'][0];

        $inventoryItem = DB::table('inventory_items')->where('id', $inventoryItemId)->first();
        $this->assertNotNull($inventoryItem);
        $this->assertSame('item-property', $inventoryItem->item_id);
        $this->assertSame('air-unit-1', $inventoryItem->air_item_unit_id);
        $this->assertSame('dept-gso', $inventoryItem->department_id);
        $this->assertSame('fund-1', $inventoryItem->fund_source_id);
        $this->assertSame(0, (int) $inventoryItem->is_ics);
        $this->assertSame(InventoryCustodyStates::POOL, $inventoryItem->custody_state);
        $this->assertSame(InventoryStatuses::SERVICEABLE, $inventoryItem->status);
        $this->assertSame(InventoryConditions::GOOD, $inventoryItem->condition);
        $this->assertSame('GSO Pool', $inventoryItem->accountable_officer);
        $this->assertSame('officer-1', $inventoryItem->accountable_officer_id);
        $this->assertSame('inventory-drive-folder-1', $inventoryItem->drive_folder_id);
        $this->assertMatchesRegularExpression('/^2026-ICT-PPE-00001$/', (string) $inventoryItem->property_number);

        $this->assertDatabaseHas('air_item_units', [
            'id' => 'air-unit-1',
            'inventory_item_id' => $inventoryItemId,
            'property_number' => $inventoryItem->property_number,
        ]);

        $this->assertDatabaseHas('inventory_item_files', [
            'inventory_item_id' => $inventoryItemId,
            'path' => 'copied-drive-file-1',
            'driver' => 'google',
            'type' => 'photo',
            'original_name' => 'front-view.jpg',
        ]);
        $this->assertDatabaseHas('inventory_item_components', [
            'inventory_item_id' => $inventoryItemId,
            'name' => 'Laptop Body',
            'serial_number' => 'SN-LAPTOP-001',
            'is_present' => 1,
        ]);

        $this->assertDatabaseHas('stocks', [
            'item_id' => 'item-consumable',
            'fund_source_id' => 'fund-1',
            'on_hand' => 20,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'item_id' => 'item-consumable',
            'fund_source_id' => 'fund-1',
            'movement_type' => 'in',
            'qty' => 20,
            'reference_type' => 'AIR',
            'reference_id' => 'air-1',
            'air_item_id' => 'air-item-consumable',
            'created_by_name' => 'gsoadmin',
        ]);

        $remainingEligibility = $service->getEligibility('air-1');
        $this->assertSame(0, $remainingEligibility['summary']['property_units_count']);
        $this->assertSame(0, $remainingEligibility['summary']['consumable_lines_count']);
    }

    public function test_it_requires_an_inspected_air_before_promotion(): void
    {
        $this->seedPromotionData(status: AirStatuses::SUBMITTED);

        $events = Mockery::mock(InventoryItemEventServiceInterface::class);
        $events->shouldNotReceive('create');

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $driveFolders = Mockery::mock(GoogleDriveFolderServiceInterface::class);
        $driveFolders->shouldNotReceive('ensureFolder');

        $driveFiles = Mockery::mock(GoogleDriveFileServiceInterface::class);
        $driveFiles->shouldNotReceive('copyFile');

        $service = new AirInventoryPromotionService(
            new EloquentInventoryItemRepository(),
            new EloquentInventoryItemFileRepository(),
            $events,
            new EloquentStockRepository(),
            new EloquentStockMovementRepository(),
            new AssetComponentService(),
            $audit,
            $driveFolders,
            $driveFiles,
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Only inspected AIR records can be promoted into inventory');

        $service->promote('actor-1', 'air-1');
    }

    private function seedPromotionData(string $status = AirStatuses::INSPECTED): void
    {
        DB::table('departments')->insert([
            [
                'id' => 'dept-gso',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'short_name' => 'GSO',
                'type' => 'office',
                'parent_department_id' => null,
                'head_user_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'dept-req',
                'code' => 'ICT',
                'name' => 'Information and Communications Technology Office',
                'short_name' => 'ICT',
                'type' => 'office',
                'parent_department_id' => null,
                'head_user_id' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('accountable_officers')->insert([
            'id' => 'officer-1',
            'full_name' => 'GSO Pool',
            'normalized_name' => 'gso pool',
            'designation' => 'Supply Officer',
            'office' => 'General Services Office',
            'department_id' => 'dept-gso',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('fund_clusters')->insert([
            'id' => 'cluster-1',
            'code' => 'FC-01',
            'name' => 'General Fund Cluster',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('fund_sources')->insert([
            'id' => 'fund-1',
            'name' => 'General Fund',
            'code' => 'GF',
            'fund_cluster_id' => 'cluster-1',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('asset_categories')->insert([
            'id' => 'asset-1',
            'asset_type_id' => null,
            'asset_code' => 'ICT',
            'asset_name' => 'ICT Equipment',
            'account_group' => 'PPE',
            'is_selected' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            [
                'id' => 'item-property',
                'asset_id' => 'asset-1',
                'item_name' => 'Laptop Computer',
                'description' => 'Portable workstation',
                'base_unit' => 'unit',
                'item_identification' => 'ITM-ICT-001',
                'tracking_type' => 'property',
                'requires_serial' => true,
                'is_semi_expendable' => false,
                'is_selected' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'item-consumable',
                'asset_id' => null,
                'item_name' => 'Bond Paper',
                'description' => 'Office supply',
                'base_unit' => 'ream',
                'item_identification' => 'SUP-001',
                'tracking_type' => 'consumable',
                'requires_serial' => false,
                'is_semi_expendable' => false,
                'is_selected' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('item_unit_conversions')->insert([
            'id' => 'conversion-1',
            'item_id' => 'item-consumable',
            'from_unit' => 'box',
            'multiplier' => 10,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('airs')->insert([
            'id' => 'air-1',
            'parent_air_id' => null,
            'continuation_no' => 1,
            'po_number' => 'PO-2026-001',
            'po_date' => '2026-03-10',
            'air_number' => 'AIR-2026-0001',
            'air_date' => '2026-03-10',
            'invoice_number' => 'INV-001',
            'invoice_date' => '2026-03-10',
            'supplier_name' => 'Supply Hub',
            'requesting_department_id' => 'dept-req',
            'requesting_department_name_snapshot' => 'Information and Communications Technology Office',
            'requesting_department_code_snapshot' => 'ICT',
            'fund_source_id' => 'fund-1',
            'fund' => 'General Fund',
            'status' => $status,
            'date_received' => '2026-03-15',
            'received_completeness' => 'complete',
            'received_notes' => 'Complete delivery',
            'date_inspected' => '2026-03-16',
            'inspection_verified' => true,
            'inspection_notes' => 'Ready',
            'inspected_by_name' => 'Juan Inspector',
            'accepted_by_name' => 'GSO Pool',
            'created_by_user_id' => 'actor-1',
            'created_by_name_snapshot' => 'gsoadmin',
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            [
                'id' => 'air-item-property',
                'air_id' => 'air-1',
                'item_id' => 'item-property',
                'stock_no_snapshot' => 'ITM-ICT-001',
                'item_name_snapshot' => 'Laptop Computer',
                'description_snapshot' => 'Portable workstation',
                'unit_snapshot' => 'unit',
                'acquisition_cost' => 75000,
                'qty_ordered' => 1,
                'qty_delivered' => 1,
                'qty_accepted' => 1,
                'tracking_type_snapshot' => 'property',
                'requires_serial_snapshot' => true,
                'is_semi_expendable_snapshot' => false,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'air-item-consumable',
                'air_id' => 'air-1',
                'item_id' => 'item-consumable',
                'stock_no_snapshot' => 'SUP-001',
                'item_name_snapshot' => 'Bond Paper',
                'description_snapshot' => 'Office supply',
                'unit_snapshot' => 'box',
                'acquisition_cost' => 1500,
                'qty_ordered' => 2,
                'qty_delivered' => 2,
                'qty_accepted' => 2,
                'tracking_type_snapshot' => 'consumable',
                'requires_serial_snapshot' => false,
                'is_semi_expendable_snapshot' => false,
                'remarks' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('air_item_units')->insert([
            'id' => 'air-unit-1',
            'air_item_id' => 'air-item-property',
            'inventory_item_id' => null,
            'brand' => 'Dell',
            'model' => 'Latitude 5450',
            'serial_number' => 'SN-LAPTOP-001',
            'property_number' => null,
            'condition_status' => InventoryConditions::GOOD,
            'condition_notes' => 'Unit accepted and ready.',
            'drive_folder_id' => 'air-unit-drive-folder-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_item_unit_files')->insert([
            'id' => 'air-unit-file-1',
            'air_item_unit_id' => 'air-unit-1',
            'driver' => 'google',
            'path' => 'drive-air-unit-file-1',
            'type' => 'photo',
            'is_primary' => true,
            'position' => 1,
            'original_name' => 'front-view.jpg',
            'mime' => 'image/jpeg',
            'size' => 14000,
            'caption' => 'Front view',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_item_unit_components')->insert([
            'id' => 'air-unit-component-1',
            'air_item_unit_id' => 'air-unit-1',
            'line_no' => 1,
            'name' => 'Laptop Body',
            'quantity' => 1,
            'unit' => 'unit',
            'component_cost' => 75000,
            'serial_number' => 'SN-LAPTOP-001',
            'condition' => InventoryConditions::GOOD,
            'is_present' => true,
            'remarks' => 'Main assembled unit',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('short_name', 255)->nullable();
            $table->string('type', 50)->nullable();
            $table->uuid('parent_department_id')->nullable();
            $table->uuid('head_user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('accountable_officers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name', 255);
            $table->string('normalized_name', 255)->nullable();
            $table->string('designation', 255)->nullable();
            $table->string('office', 255)->nullable();
            $table->uuid('department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('code', 30)->nullable();
            $table->uuid('fund_cluster_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asset_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_type_id')->nullable();
            $table->string('asset_code', 50)->nullable();
            $table->string('asset_name', 255)->nullable();
            $table->string('account_group', 50)->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

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
            $table->boolean('is_selected')->default(false);
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

        Schema::create('airs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_air_id')->nullable();
            $table->unsignedInteger('continuation_no')->default(1);
            $table->string('po_number', 255)->nullable();
            $table->date('po_date')->nullable();
            $table->string('air_number', 255)->nullable();
            $table->date('air_date')->nullable();
            $table->string('invoice_number', 255)->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('supplier_name', 255)->nullable();
            $table->uuid('requesting_department_id')->nullable();
            $table->string('requesting_department_name_snapshot', 255)->nullable();
            $table->string('requesting_department_code_snapshot', 50)->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('fund', 255)->nullable();
            $table->string('status', 50)->default('draft');
            $table->date('date_received')->nullable();
            $table->string('received_completeness', 50)->nullable();
            $table->text('received_notes')->nullable();
            $table->date('date_inspected')->nullable();
            $table->boolean('inspection_verified')->default(false);
            $table->text('inspection_notes')->nullable();
            $table->string('inspected_by_name', 255)->nullable();
            $table->string('accepted_by_name', 255)->nullable();
            $table->uuid('created_by_user_id')->nullable();
            $table->string('created_by_name_snapshot', 255)->nullable();
            $table->text('remarks')->nullable();
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
            $table->decimal('acquisition_cost', 15, 2)->nullable();
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

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id')->nullable();
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

        Schema::create('inventory_item_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
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

        Schema::create('inventory_item_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
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

        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('fund_source_id')->nullable();
            $table->unsignedInteger('on_hand')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('fund_source_id')->nullable();
            $table->string('movement_type', 50);
            $table->unsignedInteger('qty')->default(0);
            $table->string('reference_type', 50)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->uuid('air_item_id')->nullable();
            $table->uuid('ris_item_id')->nullable();
            $table->dateTime('occurred_at')->nullable();
            $table->string('created_by_name', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
