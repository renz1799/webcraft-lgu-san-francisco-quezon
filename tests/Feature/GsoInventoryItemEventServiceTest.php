<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemEventRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemRepository;
use App\Modules\GSO\Services\InventoryItemEventService;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoInventoryItemEventServiceTest extends TestCase
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

    public function test_it_creates_and_lists_inventory_item_events_with_snapshots(): void
    {
        DB::table('users')->insert([
            'id' => 'actor-1',
            'username' => 'inventory.clerk',
            'email' => 'inventory@example.com',
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

        DB::table('fund_sources')->insert([
            'id' => 'fund-1',
            'code' => 'GF',
            'name' => 'General Fund',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('inventory_items')->insert([
            'id' => 'inventory-1',
            'item_id' => 'item-1',
            'department_id' => 'dept-1',
            'fund_source_id' => 'fund-1',
            'property_number' => 'PROP-001',
            'acquisition_date' => '2026-03-01',
            'acquisition_cost' => 55000.25,
            'description' => 'Issued inventory item',
            'quantity' => 1,
            'unit' => 'unit',
            'stock_number' => 'STK-001',
            'service_life' => 5,
            'is_ics' => false,
            'accountable_officer' => 'Maria Clara',
            'accountable_officer_id' => null,
            'custody_state' => 'issued',
            'status' => InventoryStatuses::SERVICEABLE,
            'condition' => InventoryConditions::GOOD,
            'brand' => 'Lenovo',
            'model' => 'ThinkPad',
            'serial_number' => 'SN-001',
            'po_number' => 'PO-001',
            'drive_folder_id' => 'drive-folder-1',
            'remarks' => 'Ready for release',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->once();

        $service = new InventoryItemEventService(
            new EloquentInventoryItemRepository(),
            new EloquentInventoryItemEventRepository(),
            $audit,
        );

        $created = $service->create('actor-1', 'inventory-1', [
            'event_type' => InventoryEventTypes::ISSUED,
            'event_date' => '2026-03-21 14:30:00',
            'quantity' => 1,
            'person_accountable' => 'Maria Clara',
            'reference_type' => 'PAR',
            'reference_no' => 'PAR-2026-001',
            'notes' => 'Issued to requesting office.',
        ]);

        DB::table('inventory_item_event_files')->insert([
            'id' => 'event-file-1',
            'inventory_item_event_id' => (string) $created->id,
            'disk' => 'google',
            'path' => 'events/event-file-1.pdf',
            'drive_file_id' => 'drive-event-file-1',
            'drive_web_view_link' => 'https://example.test/view/event-file-1',
            'original_name' => 'par-scan.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 2048,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('inventory_item_events', [
            'id' => (string) $created->id,
            'inventory_item_id' => 'inventory-1',
            'department_id' => 'dept-1',
            'performed_by_user_id' => 'actor-1',
            'event_type' => InventoryEventTypes::ISSUED,
            'qty_in' => 0,
            'qty_out' => 1,
            'amount_snapshot' => 55000.25,
            'unit_snapshot' => 'unit',
            'office_snapshot' => 'GSO - General Services Office',
            'officer_snapshot' => 'Maria Clara',
            'reference_type' => 'PAR',
            'reference_no' => 'PAR-2026-001',
        ]);

        $payload = $service->listForInventoryItem('inventory-1');

        $this->assertSame(1, $payload['inventory_item']['event_count']);
        $this->assertCount(1, $payload['events']);
        $this->assertSame('Issued', $payload['events'][0]['event_type_text']);
        $this->assertSame('-1', $payload['events'][0]['movement_text']);
        $this->assertSame('PAR: PAR-2026-001', $payload['events'][0]['reference_label']);
        $this->assertSame('GSO - General Services Office', $payload['events'][0]['department_label']);
        $this->assertSame('inventory.clerk (inventory@example.com)', $payload['events'][0]['performed_by_label']);
        $this->assertSame('GF - General Fund', $payload['events'][0]['fund_source_label']);
        $this->assertSame(1, $payload['events'][0]['file_count']);
    }

    public function test_it_rejects_zero_quantity_for_non_metadata_event_types(): void
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
            'description' => 'Issued inventory item',
            'quantity' => 1,
            'unit' => 'unit',
            'stock_number' => null,
            'service_life' => null,
            'is_ics' => false,
            'accountable_officer' => null,
            'accountable_officer_id' => null,
            'custody_state' => 'issued',
            'status' => InventoryStatuses::SERVICEABLE,
            'condition' => InventoryConditions::GOOD,
            'brand' => null,
            'model' => null,
            'serial_number' => null,
            'po_number' => 'PO-001',
            'drive_folder_id' => null,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $service = new InventoryItemEventService(
            new EloquentInventoryItemRepository(),
            new EloquentInventoryItemEventRepository(),
            $audit,
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Quantity must be greater than zero for this event type.');

        $service->create('actor-1', 'inventory-1', [
            'event_type' => InventoryEventTypes::ISSUED,
            'event_date' => '2026-03-21 14:30:00',
            'quantity' => 0,
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

        Schema::create('inventory_item_event_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_event_id');
            $table->string('disk', 50)->nullable();
            $table->string('path', 255)->nullable();
            $table->string('drive_file_id', 120)->nullable();
            $table->string('drive_web_view_link', 255)->nullable();
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
