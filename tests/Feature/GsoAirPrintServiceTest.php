<?php

namespace Tests\Feature;

use App\Modules\GSO\Services\AirPrintService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GsoAirPrintServiceTest extends TestCase
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

    public function test_air_print_service_builds_unit_and_consumable_rows(): void
    {
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

        DB::table('items')->insert([
            [
                'id' => 'item-1',
                'item_name' => 'Laptop Computer',
                'item_identification' => 'ITM-001',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'item-2',
                'item_name' => 'Bond Paper',
                'item_identification' => 'OFF-001',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('airs')->insert([
            'id' => 'air-1',
            'parent_air_id' => null,
            'continuation_no' => 1,
            'po_number' => 'PO-2026-001',
            'po_date' => '2026-03-21',
            'air_number' => 'AIR-2026-0001',
            'air_date' => '2026-03-21',
            'invoice_number' => 'INV-001',
            'invoice_date' => '2026-03-20',
            'supplier_name' => 'Acme Trading',
            'requesting_department_id' => 'dept-1',
            'requesting_department_name_snapshot' => 'General Services Office',
            'requesting_department_code_snapshot' => 'GSO',
            'fund_source_id' => 'fund-1',
            'fund' => 'General Fund',
            'status' => 'inspected',
            'date_received' => '2026-03-21',
            'received_completeness' => 'complete',
            'date_inspected' => '2026-03-21',
            'inspection_verified' => true,
            'inspected_by_name' => 'Juan Dela Cruz',
            'accepted_by_name' => 'Maria Clara',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            [
                'id' => 'air-item-1',
                'air_id' => 'air-1',
                'item_id' => 'item-1',
                'stock_no_snapshot' => 'ITM-001',
                'item_name_snapshot' => 'Laptop Computer',
                'description_snapshot' => 'Dell Latitude 7440',
                'unit_snapshot' => 'unit',
                'qty_ordered' => 2,
                'qty_delivered' => 2,
                'qty_accepted' => 2,
                'tracking_type_snapshot' => 'property',
                'requires_serial_snapshot' => true,
                'is_semi_expendable_snapshot' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'air-item-2',
                'air_id' => 'air-1',
                'item_id' => 'item-2',
                'stock_no_snapshot' => 'OFF-001',
                'item_name_snapshot' => 'Bond Paper',
                'description_snapshot' => 'A4 Bond Paper',
                'unit_snapshot' => 'ream',
                'qty_ordered' => 40,
                'qty_delivered' => 40,
                'qty_accepted' => 35,
                'tracking_type_snapshot' => 'consumable',
                'requires_serial_snapshot' => false,
                'is_semi_expendable_snapshot' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('air_item_units')->insert([
            [
                'id' => 'unit-1',
                'air_item_id' => 'air-item-1',
                'serial_number' => 'SN-001',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'unit-2',
                'air_item_id' => 'air-item-1',
                'serial_number' => 'SN-002',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $payload = (new AirPrintService())->getPrintViewData('air-1');

        $this->assertSame('GF - General Fund', $payload['print']['fund_source']);
        $this->assertSame('GSO - General Services Office', $payload['print']['office_department']);
        $this->assertSame('Acme Trading', $payload['print']['supplier']);
        $this->assertCount(3, $payload['rows']);
        $this->assertSame([1, 1, 35], collect($payload['rows'])->pluck('quantity')->sort()->values()->all());
        $this->assertSame(1, $payload['totalPages']);
    }

    public function test_air_print_service_adds_continuation_rows_when_multiple_pages_are_needed(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-2',
            'parent_air_id' => null,
            'continuation_no' => 1,
            'po_number' => 'PO-2026-099',
            'po_date' => '2026-03-21',
            'air_number' => 'AIR-2026-0099',
            'air_date' => '2026-03-21',
            'supplier_name' => 'Acme Trading',
            'status' => 'submitted',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'bulk-item',
            'item_name' => 'Plastic Chair',
            'item_identification' => 'FUR-001',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $rows = [];
        for ($index = 1; $index <= 25; $index++) {
            $rows[] = [
                'id' => 'air-item-bulk-' . $index,
                'air_id' => 'air-2',
                'item_id' => 'bulk-item',
                'stock_no_snapshot' => 'FUR-' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'item_name_snapshot' => 'Plastic Chair ' . $index,
                'description_snapshot' => 'Plastic Chair ' . $index,
                'unit_snapshot' => 'piece',
                'qty_ordered' => 1,
                'qty_delivered' => 0,
                'qty_accepted' => 0,
                'tracking_type_snapshot' => 'consumable',
                'requires_serial_snapshot' => false,
                'is_semi_expendable_snapshot' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('air_items')->insert($rows);

        $payload = (new AirPrintService())->getPrintViewData('air-2');

        $this->assertSame(2, $payload['totalPages']);
        $this->assertTrue((bool) ($payload['pages'][0][22]['__msg'] ?? false));
        $this->assertSame('*** CONTINUED ON PAGE 2 ***', $payload['pages'][0][22]['description']);
        $this->assertTrue((bool) ($payload['pages'][1][0]['__msg'] ?? false));
        $this->assertSame('*** CONTINUATION FROM PAGE 1 ***', $payload['pages'][1][0]['description']);
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

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

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_name', 255)->nullable();
            $table->string('item_identification', 255)->nullable();
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
            $table->string('requesting_department_code_snapshot', 100)->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('fund', 255)->nullable();
            $table->string('status', 50)->nullable();
            $table->date('date_received')->nullable();
            $table->string('received_completeness', 50)->nullable();
            $table->text('received_notes')->nullable();
            $table->date('date_inspected')->nullable();
            $table->boolean('inspection_verified')->nullable();
            $table->string('inspected_by_name', 255)->nullable();
            $table->string('accepted_by_name', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('air_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_id');
            $table->uuid('item_id')->nullable();
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
            $table->string('drive_folder_id', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
