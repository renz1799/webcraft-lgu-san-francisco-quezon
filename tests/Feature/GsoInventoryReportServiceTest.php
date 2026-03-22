<?php

namespace Tests\Feature;

use App\Modules\GSO\Services\RegspiReportService;
use App\Modules\GSO\Services\RpcppeReportService;
use App\Modules\GSO\Services\RpcspReportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GsoInventoryReportServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('app.lgu_name', 'Municipality of San Francisco');
        config()->set('gso.gso_designate_name', 'Ana Reyes');
        config()->set('gso.gso_designate_designation', 'GSO Focal Person');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        $this->seedBaseData();
    }

    public function test_regspi_service_filters_issued_semi_expendable_items_and_builds_summary(): void
    {
        DB::table('inventory_items')->insert([
            $this->inventoryItemRow('inventory-se-issued', [
                'item_id' => 'item-se',
                'department_id' => 'dept-ict',
                'fund_source_id' => 'fund-gf',
                'property_number' => 'SE-001',
                'quantity' => 2,
                'acquisition_cost' => 3000,
                'is_ics' => true,
                'accountable_officer_id' => 'officer-juan',
                'accountable_officer' => 'Juan Dela Cruz',
                'custody_state' => 'issued',
            ]),
            $this->inventoryItemRow('inventory-se-returned', [
                'item_id' => 'item-se',
                'department_id' => 'dept-ict',
                'fund_source_id' => 'fund-gf',
                'property_number' => 'SE-002',
                'quantity' => 1,
                'acquisition_cost' => 1200,
                'is_ics' => true,
                'accountable_officer_id' => 'officer-juan',
                'accountable_officer' => 'Juan Dela Cruz',
                'custody_state' => 'issued',
            ]),
        ]);

        DB::table('inventory_item_events')->insert([
            $this->inventoryEventRow('event-se-issued', [
                'inventory_item_id' => 'inventory-se-issued',
                'department_id' => 'dept-ict',
                'event_type' => 'issued',
                'event_date' => '2026-03-02 09:00:00',
                'qty_out' => 2,
                'amount_snapshot' => 1500,
                'office_snapshot' => 'ICT - ICT Office',
                'officer_snapshot' => 'Juan Dela Cruz',
                'status' => 'serviceable',
                'condition' => 'good',
                'reference_type' => 'ICS',
                'reference_no' => 'ICS-2026-001',
            ]),
            $this->inventoryEventRow('event-se-returned-issued', [
                'inventory_item_id' => 'inventory-se-returned',
                'department_id' => 'dept-ict',
                'event_type' => 'issued',
                'event_date' => '2026-03-01 08:00:00',
                'qty_out' => 1,
                'amount_snapshot' => 1200,
                'office_snapshot' => 'ICT - ICT Office',
                'officer_snapshot' => 'Juan Dela Cruz',
                'status' => 'serviceable',
                'condition' => 'good',
                'reference_type' => 'ICS',
                'reference_no' => 'ICS-2026-002',
            ]),
            $this->inventoryEventRow('event-se-returned-return', [
                'inventory_item_id' => 'inventory-se-returned',
                'department_id' => 'dept-ict',
                'event_type' => 'returned',
                'event_date' => '2026-03-10 08:00:00',
                'qty_in' => 1,
                'amount_snapshot' => 1200,
                'office_snapshot' => 'ICT - ICT Office',
                'officer_snapshot' => 'Juan Dela Cruz',
                'status' => 'serviceable',
                'condition' => 'good',
                'reference_type' => 'ITR',
                'reference_no' => 'ITR-2026-001',
            ]),
        ]);

        $service = new RegspiReportService();

        $payload = $service->getPrintViewData(
            fundSourceId: 'fund-gf',
            departmentId: 'dept-ict',
            accountableOfficerId: 'officer-juan',
            asOf: '2026-03-21',
        );

        $this->assertSame('RegSPI', $payload['report']['appendix_label']);
        $this->assertSame('Ana Reyes', $payload['report']['signatories']['prepared_by_name']);
        $this->assertCount(1, $payload['rows']);
        $this->assertSame('SE-001', $payload['rows'][0]['property_no']);
        $this->assertSame('ICS: ICS-2026-001', $payload['rows'][0]['reference']);
        $this->assertSame(1500.0, $payload['rows'][0]['unit_value']);
        $this->assertSame(1, $payload['report']['summary']['total_items']);
        $this->assertSame(2, $payload['report']['summary']['total_qty']);
        $this->assertSame(3000.0, $payload['report']['summary']['total_value']);
    }

    public function test_rpcppe_service_builds_prefilled_property_count_for_ppe_items_only(): void
    {
        DB::table('inventory_items')->insert([
            $this->inventoryItemRow('inventory-ppe-active', [
                'item_id' => 'item-ppe',
                'department_id' => 'dept-gso',
                'fund_source_id' => 'fund-gf',
                'property_number' => 'PPE-001',
                'quantity' => 2,
                'acquisition_cost' => 8000,
                'is_ics' => false,
                'accountable_officer' => 'Maria Clara',
                'status' => 'serviceable',
            ]),
            $this->inventoryItemRow('inventory-ppe-disposed', [
                'item_id' => 'item-ppe',
                'department_id' => 'dept-gso',
                'fund_source_id' => 'fund-gf',
                'property_number' => 'PPE-002',
                'quantity' => 1,
                'acquisition_cost' => 2200,
                'is_ics' => false,
                'accountable_officer' => 'Maria Clara',
                'status' => 'disposed',
            ]),
        ]);

        DB::table('inventory_item_events')->insert([
            $this->inventoryEventRow('event-ppe-active', [
                'inventory_item_id' => 'inventory-ppe-active',
                'department_id' => 'dept-gso',
                'event_type' => 'acquired',
                'event_date' => '2026-03-05 10:00:00',
                'qty_in' => 2,
                'amount_snapshot' => 4000,
                'office_snapshot' => 'GSO - General Services Office',
                'officer_snapshot' => 'Maria Clara',
                'status' => 'serviceable',
                'condition' => 'good',
                'reference_type' => 'PAR',
                'reference_no' => 'PAR-2026-001',
            ]),
            $this->inventoryEventRow('event-ppe-disposed', [
                'inventory_item_id' => 'inventory-ppe-disposed',
                'department_id' => 'dept-gso',
                'event_type' => 'disposed',
                'event_date' => '2026-03-18 10:00:00',
                'qty_out' => 1,
                'amount_snapshot' => 2200,
                'office_snapshot' => 'GSO - General Services Office',
                'officer_snapshot' => 'Maria Clara',
                'status' => 'disposed',
                'condition' => 'poor',
                'reference_type' => 'WMR',
                'reference_no' => 'WMR-2026-001',
            ]),
        ]);

        $service = new RpcppeReportService();

        $payload = $service->getPrintViewData(
            fundSourceId: 'fund-gf',
            asOf: '2026-03-21',
            prefillCount: true,
        );

        $this->assertSame('RPCPPE', $payload['report']['appendix_label']);
        $this->assertCount(1, $payload['rows']);
        $this->assertSame('PPE-001', $payload['rows'][0]['property_no']);
        $this->assertSame(2, $payload['rows'][0]['count_qty']);
        $this->assertSame(0, $payload['rows'][0]['shortage_overage_qty']);
        $this->assertSame(4000.0, $payload['rows'][0]['unit_value']);
        $this->assertSame(8000.0, $payload['report']['summary']['total_book_value']);
        $this->assertSame(2, $payload['report']['summary']['total_count_qty']);
    }

    public function test_rpcsp_service_defaults_signatory_from_selected_officer(): void
    {
        DB::table('inventory_items')->insert(
            $this->inventoryItemRow('inventory-se-active', [
                'item_id' => 'item-se',
                'department_id' => 'dept-ict',
                'fund_source_id' => 'fund-gf',
                'property_number' => 'SE-010',
                'quantity' => 3,
                'acquisition_cost' => 4500,
                'is_ics' => true,
                'accountable_officer_id' => 'officer-juan',
                'accountable_officer' => 'Juan Dela Cruz',
                'custody_state' => 'issued',
            ])
        );

        DB::table('inventory_item_events')->insert(
            $this->inventoryEventRow('event-se-active', [
                'inventory_item_id' => 'inventory-se-active',
                'department_id' => 'dept-ict',
                'event_type' => 'issued',
                'event_date' => '2026-03-06 13:00:00',
                'qty_out' => 3,
                'amount_snapshot' => 1500,
                'office_snapshot' => 'ICT - ICT Office',
                'officer_snapshot' => 'Juan Dela Cruz',
                'status' => 'serviceable',
                'condition' => 'good',
                'reference_type' => 'ICS',
                'reference_no' => 'ICS-2026-011',
            ])
        );

        $service = new RpcspReportService();

        $payload = $service->getPrintViewData(
            fundSourceId: 'fund-gf',
            departmentId: 'dept-ict',
            accountableOfficerId: 'officer-juan',
            asOf: '2026-03-21',
            prefillCount: false,
        );

        $this->assertSame('RPCSP', $payload['report']['appendix_label']);
        $this->assertCount(1, $payload['rows']);
        $this->assertSame('SE-010', $payload['rows'][0]['property_no']);
        $this->assertNull($payload['rows'][0]['count_qty']);
        $this->assertSame('Juan Dela Cruz', $payload['report']['signatories']['accountable_officer_name']);
        $this->assertSame('Supply Officer', $payload['report']['signatories']['accountable_officer_designation']);
        $this->assertNull($payload['report']['summary']['total_count_qty']);
    }

    private function seedBaseData(): void
    {
        DB::table('departments')->insert([
            [
                'id' => 'dept-gso',
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
                'id' => 'dept-ict',
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

        DB::table('fund_clusters')->insert([
            [
                'id' => 'cluster-1',
                'name' => 'General Fund Cluster',
                'code' => 'FC-01',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('fund_sources')->insert([
            [
                'id' => 'fund-gf',
                'name' => 'General Fund',
                'code' => 'GF',
                'fund_cluster_id' => 'cluster-1',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('accountable_officers')->insert([
            [
                'id' => 'officer-juan',
                'full_name' => 'Juan Dela Cruz',
                'normalized_name' => 'juan dela cruz',
                'designation' => 'Supply Officer',
                'office' => 'ICT',
                'department_id' => 'dept-ict',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('items')->insert([
            [
                'id' => 'item-ppe',
                'item_name' => 'Laptop Computer',
                'item_identification' => 'LT-001',
                'tracking_type' => 'property',
                'requires_serial' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'item-se',
                'item_name' => 'Projector',
                'item_identification' => 'PJ-001',
                'tracking_type' => 'property',
                'requires_serial' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function inventoryItemRow(string $id, array $overrides = []): array
    {
        return array_merge([
            'id' => $id,
            'item_id' => 'item-ppe',
            'air_item_unit_id' => null,
            'department_id' => 'dept-gso',
            'fund_source_id' => 'fund-gf',
            'property_number' => $id,
            'acquisition_date' => '2026-03-01',
            'acquisition_cost' => 1000,
            'description' => 'Inventory Item ' . $id,
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
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function inventoryEventRow(string $id, array $overrides = []): array
    {
        return array_merge([
            'id' => $id,
            'inventory_item_id' => 'inventory-ppe-active',
            'department_id' => 'dept-gso',
            'performed_by_user_id' => null,
            'event_type' => 'acquired',
            'event_date' => '2026-03-01 08:00:00',
            'qty_in' => 1,
            'qty_out' => 0,
            'amount_snapshot' => 1000,
            'unit_snapshot' => 'unit',
            'office_snapshot' => 'GSO - General Services Office',
            'officer_snapshot' => null,
            'status' => 'serviceable',
            'condition' => 'good',
            'person_accountable' => null,
            'notes' => null,
            'reference_type' => null,
            'reference_no' => null,
            'reference_id' => null,
            'fund_source_id' => 'fund-gf',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ], $overrides);
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('type', 50)->nullable();
            $table->uuid('parent_department_id')->nullable();
            $table->uuid('head_user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('code', 30)->nullable();
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

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_name');
            $table->string('item_identification')->nullable();
            $table->string('tracking_type', 50)->default('property');
            $table->boolean('requires_serial')->default(false);
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
