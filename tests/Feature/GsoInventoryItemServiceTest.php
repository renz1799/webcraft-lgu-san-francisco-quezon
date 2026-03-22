<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\InventoryItemDatatableRowBuilder;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemRepository;
use App\Modules\GSO\Services\InventoryItemService;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoInventoryItemServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_inventory_item_service_handles_crud_filters_and_accountable_officer_resolution(): void
    {
        DB::table('items')->insert([
            [
                'id' => 'item-property',
                'item_name' => 'Laptop Computer',
                'item_identification' => 'LT-001',
                'tracking_type' => 'property',
                'requires_serial' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'item-property-2',
                'item_name' => 'Document Camera',
                'item_identification' => 'DC-002',
                'tracking_type' => 'property',
                'requires_serial' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('departments')->insert([
            [
                'id' => 'dept-1',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'short_name' => 'GSO',
                'type' => 'office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'dept-2',
                'code' => 'ICT',
                'name' => 'ICT Office',
                'short_name' => 'ICT',
                'type' => 'office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('fund_sources')->insert([
            [
                'id' => 'fund-1',
                'code' => 'GF',
                'name' => 'General Fund',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'fund-2',
                'code' => 'SEF',
                'name' => 'Special Education Fund',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('accountable_officers')->insert([
            [
                'id' => 'officer-1',
                'full_name' => 'Juan Dela Cruz',
                'normalized_name' => 'juan dela cruz',
                'designation' => 'Supply Officer',
                'office' => 'GSO',
                'department_id' => 'dept-1',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(4);

        $service = new InventoryItemService(
            new EloquentInventoryItemRepository(),
            $audit,
            new InventoryItemDatatableRowBuilder(),
        );

        $created = $service->create('actor-1', [
            'item_id' => 'item-property',
            'department_id' => 'dept-1',
            'fund_source_id' => 'fund-1',
            'property_number' => 'PROP-001',
            'acquisition_date' => '2026-03-01',
            'acquisition_cost' => 55000.25,
            'description' => 'Primary deployment unit',
            'quantity' => 1,
            'unit' => 'unit',
            'stock_number' => 'STK-001',
            'service_life' => 5,
            'is_ics' => false,
            'accountable_officer_id' => 'officer-1',
            'custody_state' => InventoryCustodyStates::POOL,
            'status' => InventoryStatuses::SERVICEABLE,
            'condition' => InventoryConditions::BRAND_NEW,
            'brand' => 'Lenovo',
            'model' => 'ThinkPad',
            'serial_number' => 'SN-001',
            'po_number' => 'PO-001',
            'remarks' => 'For deployment',
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => $created->id,
            'item_id' => 'item-property',
            'department_id' => 'dept-1',
            'fund_source_id' => 'fund-1',
            'property_number' => 'PROP-001',
            'acquisition_date' => '2026-03-01',
            'acquisition_cost' => 55000.25,
            'quantity' => 1,
            'unit' => 'unit',
            'accountable_officer_id' => 'officer-1',
            'accountable_officer' => 'Juan Dela Cruz',
            'custody_state' => InventoryCustodyStates::POOL,
            'status' => InventoryStatuses::SERVICEABLE,
            'condition' => InventoryConditions::BRAND_NEW,
            'serial_number' => 'SN-001',
            'po_number' => 'PO-001',
        ]);

        $updated = $service->update('actor-1', (string) $created->id, [
            'item_id' => 'item-property-2',
            'department_id' => 'dept-2',
            'fund_source_id' => 'fund-2',
            'property_number' => 'PROP-002',
            'acquisition_date' => '2026-03-12',
            'acquisition_cost' => 12500,
            'description' => 'Transferred to ICT',
            'quantity' => 2,
            'unit' => 'set',
            'stock_number' => 'STK-002',
            'service_life' => 3,
            'is_ics' => true,
            'accountable_officer' => 'Maria Clara',
            'custody_state' => InventoryCustodyStates::ISSUED,
            'status' => InventoryStatuses::FOR_REPAIR,
            'condition' => InventoryConditions::FAIR,
            'brand' => 'Logitech',
            'model' => 'Presenter',
            'serial_number' => 'SN-002',
            'po_number' => 'PO-002',
            'remarks' => 'Needs lens replacement',
        ]);

        $this->assertSame('item-property-2', $updated->item_id);
        $this->assertSame('dept-2', $updated->department_id);
        $this->assertSame('fund-2', $updated->fund_source_id);
        $this->assertTrue($updated->is_ics);
        $this->assertSame('Maria Clara', $updated->accountable_officer);
        $this->assertNull($updated->accountable_officer_id);
        $this->assertSame(InventoryCustodyStates::ISSUED, $updated->custody_state);
        $this->assertSame(InventoryStatuses::FOR_REPAIR, $updated->status);

        $editPayload = $service->getForEdit((string) $created->id);

        $this->assertSame('Document Camera (DC-002)', $editPayload['item_label']);
        $this->assertSame('ICT - ICT Office', $editPayload['department_label']);
        $this->assertSame('SEF - Special Education Fund', $editPayload['fund_source_label']);
        $this->assertSame('ICS', $editPayload['classification_text']);
        $this->assertSame('Issued', $editPayload['custody_state_text']);
        $this->assertSame('For Repair', $editPayload['status_text']);
        $this->assertSame('Fair', $editPayload['condition_text']);

        $filteredPayload = $service->datatable([
            'department_id' => 'dept-2',
            'classification' => 'ics',
            'custody_state' => InventoryCustodyStates::ISSUED,
            'inventory_status' => InventoryStatuses::FOR_REPAIR,
            'search' => 'prop-002',
            'archived' => 'active',
        ]);

        $this->assertSame(1, $filteredPayload['total']);
        $this->assertSame('PROP-002', $filteredPayload['data'][0]['property_number']);
        $this->assertTrue($filteredPayload['data'][0]['is_ics']);
        $this->assertSame('Maria Clara', $filteredPayload['data'][0]['accountable_officer_label']);

        $service->delete('actor-1', (string) $created->id);

        $archivedPayload = $service->datatable([
            'archived' => 'archived',
            'search' => 'prop-002',
        ]);

        $this->assertSame(1, $archivedPayload['total']);
        $this->assertTrue($archivedPayload['data'][0]['is_archived']);

        $service->restore('actor-1', (string) $created->id);

        $restoredPayload = $service->datatable([
            'archived' => 'active',
            'search' => 'prop-002',
        ]);

        $this->assertSame(1, $restoredPayload['total']);
        $this->assertFalse($restoredPayload['data'][0]['is_archived']);
    }

    public function test_inventory_item_service_rejects_non_property_items(): void
    {
        DB::table('items')->insert([
            'id' => 'item-consumable',
            'item_name' => 'Bond Paper',
            'item_identification' => 'BP-001',
            'tracking_type' => 'consumable',
            'requires_serial' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('departments')->insert([
            'id' => 'dept-1',
            'code' => 'GSO',
            'name' => 'General Services Office',
            'short_name' => 'GSO',
            'type' => 'office',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $service = new InventoryItemService(
            new EloquentInventoryItemRepository(),
            $audit,
            new InventoryItemDatatableRowBuilder(),
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Selected item is invalid or is not property-tracked.');

        $service->create('actor-1', [
            'item_id' => 'item-consumable',
            'department_id' => 'dept-1',
            'acquisition_date' => '2026-03-01',
            'acquisition_cost' => 100,
            'quantity' => 1,
            'unit' => 'ream',
            'custody_state' => InventoryCustodyStates::POOL,
            'status' => InventoryStatuses::SERVICEABLE,
            'condition' => InventoryConditions::GOOD,
            'po_number' => 'PO-003',
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

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
            $table->string('code', 50);
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('type', 50)->nullable();
            $table->uuid('parent_department_id')->nullable();
            $table->uuid('head_user_id')->nullable();
            $table->boolean('is_active')->default(true);
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

        Schema::create('accountable_officers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('normalized_name')->unique();
            $table->string('designation')->nullable();
            $table->string('office')->nullable();
            $table->uuid('department_id')->nullable();
            $table->boolean('is_active')->default(true);
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
    }
}
