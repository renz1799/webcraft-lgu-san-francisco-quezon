<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoInventoryItemImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.gso_legacy', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('gso.legacy.connection', 'gso_legacy');

        DB::purge('sqlite');
        DB::purge('gso_legacy');
        DB::reconnect('sqlite');
        DB::reconnect('gso_legacy');

        $this->createTargetSchema();
        $this->createLegacySchema();
    }

    public function test_it_imports_inventory_items_with_preserved_ids_and_dependencies(): void
    {
        DB::table('items')->insert([
            [
                'id' => 'item-1',
                'item_name' => 'Laptop Computer',
                'item_identification' => 'LT-001',
                'tracking_type' => 'property',
                'requires_serial' => true,
                'created_at' => '2026-02-01 08:00:00',
                'updated_at' => '2026-02-01 08:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'item-2',
                'item_name' => 'Document Camera',
                'item_identification' => 'DC-002',
                'tracking_type' => 'property',
                'requires_serial' => false,
                'created_at' => '2026-02-01 08:05:00',
                'updated_at' => '2026-02-01 08:05:00',
                'deleted_at' => null,
            ],
        ]);

        DB::table('departments')->insert([
            [
                'id' => 'dept-1',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'created_at' => '2026-02-01 08:00:00',
                'updated_at' => '2026-02-01 08:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'dept-2',
                'code' => 'ICT',
                'name' => 'ICT Office',
                'created_at' => '2026-02-01 08:05:00',
                'updated_at' => '2026-02-01 08:05:00',
                'deleted_at' => null,
            ],
        ]);

        DB::table('fund_sources')->insert([
            [
                'id' => 'fund-1',
                'code' => 'GF',
                'name' => 'General Fund',
                'created_at' => '2026-02-01 08:00:00',
                'updated_at' => '2026-02-01 08:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'fund-2',
                'code' => 'SEF',
                'name' => 'Special Education Fund',
                'created_at' => '2026-02-01 08:05:00',
                'updated_at' => '2026-02-01 08:05:00',
                'deleted_at' => null,
            ],
        ]);

        DB::table('accountable_officers')->insert([
            [
                'id' => 'officer-1',
                'full_name' => 'Juan Dela Cruz',
                'normalized_name' => 'juan dela cruz',
                'department_id' => 'dept-1',
                'created_at' => '2026-02-01 08:00:00',
                'updated_at' => '2026-02-01 08:00:00',
                'deleted_at' => null,
            ],
        ]);

        DB::connection('gso_legacy')->table('inventory_items')->insert([
            [
                'id' => 'inv-1',
                'item_id' => 'item-1',
                'air_item_unit_id' => null,
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
                'accountable_officer' => 'Juan Dela Cruz',
                'accountable_officer_id' => 'officer-1',
                'custody_state' => 'pool',
                'status' => 'serviceable',
                'condition' => 'brand_new',
                'brand' => 'Lenovo',
                'model' => 'ThinkPad',
                'serial_number' => 'SN-001',
                'po_number' => 'PO-001',
                'drive_folder_id' => 'drive-folder-1',
                'remarks' => 'For deployment',
                'created_at' => '2026-03-01 08:00:00',
                'updated_at' => '2026-03-01 08:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'inv-2',
                'item_id' => 'item-2',
                'air_item_unit_id' => 'air-unit-legacy-1',
                'department_id' => 'dept-2',
                'fund_source_id' => 'fund-2',
                'property_number' => 'PROP-002',
                'acquisition_date' => '2026-03-05',
                'acquisition_cost' => 12500.00,
                'description' => 'Archived inventory item',
                'quantity' => 2,
                'unit' => 'set',
                'stock_number' => 'STK-002',
                'service_life' => 3,
                'is_ics' => true,
                'accountable_officer' => 'Maria Clara',
                'accountable_officer_id' => null,
                'custody_state' => 'issued',
                'status' => 'for_repair',
                'condition' => 'fair',
                'brand' => 'Logitech',
                'model' => 'Presenter',
                'serial_number' => 'SN-002',
                'po_number' => 'PO-002',
                'drive_folder_id' => null,
                'remarks' => 'Needs repair',
                'created_at' => '2026-03-05 08:00:00',
                'updated_at' => '2026-03-05 08:00:00',
                'deleted_at' => '2026-03-20 09:00:00',
            ],
        ]);

        DB::table('inventory_items')->insert([
            'id' => 'inv-1',
            'item_id' => 'item-1',
            'department_id' => 'dept-1',
            'fund_source_id' => null,
            'property_number' => 'OLD-PROP',
            'acquisition_date' => '2026-02-20',
            'acquisition_cost' => 1000,
            'description' => null,
            'quantity' => 1,
            'unit' => 'unit',
            'stock_number' => null,
            'service_life' => null,
            'is_ics' => false,
            'accountable_officer' => null,
            'accountable_officer_id' => null,
            'custody_state' => 'pool',
            'status' => 'serviceable',
            'condition' => 'good',
            'brand' => null,
            'model' => null,
            'serial_number' => null,
            'po_number' => null,
            'drive_folder_id' => null,
            'remarks' => null,
            'created_at' => '2026-02-20 08:00:00',
            'updated_at' => '2026-02-20 08:00:00',
            'deleted_at' => null,
        ]);

        $report = (new LegacyReferenceDataImporter())->import(['inventory_items']);

        $this->assertFalse($report['summary']['dry_run']);
        $this->assertSame(1, $report['summary']['selected_tables']);
        $this->assertSame(2, $report['summary']['total_rows_read']);
        $this->assertSame(2, $report['summary']['total_rows_written']);

        $this->assertDatabaseHas('inventory_items', [
            'id' => 'inv-1',
            'property_number' => 'PROP-001',
            'fund_source_id' => 'fund-1',
            'accountable_officer_id' => 'officer-1',
            'drive_folder_id' => 'drive-folder-1',
            'po_number' => 'PO-001',
        ]);

        $this->assertDatabaseHas('inventory_items', [
            'id' => 'inv-2',
            'air_item_unit_id' => 'air-unit-legacy-1',
            'department_id' => 'dept-2',
            'fund_source_id' => 'fund-2',
            'custody_state' => 'issued',
            'status' => 'for_repair',
            'deleted_at' => '2026-03-20 09:00:00',
        ]);
    }

    public function test_it_rejects_importing_inventory_items_when_items_are_missing_from_target(): void
    {
        DB::connection('gso_legacy')->table('inventory_items')->insert([
            'id' => 'inv-missing-item',
            'item_id' => 'missing-item',
            'air_item_unit_id' => null,
            'department_id' => null,
            'fund_source_id' => null,
            'property_number' => 'PROP-404',
            'acquisition_date' => '2026-03-01',
            'acquisition_cost' => 10,
            'description' => null,
            'quantity' => 1,
            'unit' => 'unit',
            'stock_number' => null,
            'service_life' => null,
            'is_ics' => false,
            'accountable_officer' => null,
            'accountable_officer_id' => null,
            'custody_state' => 'pool',
            'status' => 'serviceable',
            'condition' => 'good',
            'brand' => null,
            'model' => null,
            'serial_number' => null,
            'po_number' => 'PO-404',
            'drive_folder_id' => null,
            'remarks' => null,
            'created_at' => '2026-03-01 08:00:00',
            'updated_at' => '2026-03-01 08:00:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced items are missing');

        (new LegacyReferenceDataImporter())->import(['inventory_items']);
    }

    private function createTargetSchema(): void
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

        Schema::create('accountable_officers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('normalized_name')->unique();
            $table->uuid('department_id')->nullable();
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
    }

    private function createLegacySchema(): void
    {
        Schema::connection('gso_legacy')->create('inventory_items', function (Blueprint $table) {
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
    }
}
