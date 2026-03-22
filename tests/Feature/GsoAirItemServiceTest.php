<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirRepository;
use App\Modules\GSO\Services\AirItemService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoAirItemServiceTest extends TestCase
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

    public function test_air_item_service_manages_draft_item_rows_and_suggestions(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-1',
            'po_number' => 'PO-2026-020',
            'po_date' => '2026-03-21',
            'air_date' => '2026-03-21',
            'supplier_name' => 'Acme Trading',
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            [
                'id' => 'item-1',
                'item_name' => 'Laptop',
                'description' => 'Portable computer',
                'base_unit' => 'piece',
                'item_identification' => 'ITM-001',
                'tracking_type' => 'property',
                'requires_serial' => true,
                'is_semi_expendable' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'item-2',
                'item_name' => 'Bond Paper',
                'description' => 'A4 paper ream',
                'base_unit' => 'ream',
                'item_identification' => 'SUP-001',
                'tracking_type' => 'consumable',
                'requires_serial' => false,
                'is_semi_expendable' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('item_unit_conversions')->insert([
            [
                'id' => 'conv-1',
                'item_id' => 'item-1',
                'from_unit' => 'box',
                'multiplier' => 5,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'conv-2',
                'item_id' => 'item-2',
                'from_unit' => 'carton',
                'multiplier' => 10,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(4);

        $service = new AirItemService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            $audit,
        );

        $suggestions = $service->suggestItems('air-1', 'lap');

        $this->assertCount(1, $suggestions);
        $this->assertSame('Laptop', $suggestions[0]['item_name']);
        $this->assertCount(2, $suggestions[0]['available_units']);

        $created = $service->addItemToDraft('user-1', 'air-1', [
            'item_id' => 'item-1',
            'qty_ordered' => 2,
            'unit_snapshot' => 'box',
            'acquisition_cost' => 2500.75,
            'description_snapshot' => 'Procured laptop set',
        ]);

        $this->assertDatabaseHas('air_items', [
            'id' => $created->id,
            'air_id' => 'air-1',
            'item_id' => 'item-1',
            'unit_snapshot' => 'box',
            'qty_ordered' => 2,
        ]);

        $list = $service->listForAir('air-1');

        $this->assertCount(1, $list);
        $this->assertSame('Laptop (ITM-001)', $list[0]['item_label']);
        $this->assertCount(2, $list[0]['available_units']);

        $updated = $service->updateItemInDraft('user-1', 'air-1', (string) $created->id, [
            'description_snapshot' => 'Procured laptop bundle',
            'qty_ordered' => 3,
            'unit_snapshot' => 'piece',
            'acquisition_cost' => 2300.50,
        ]);

        $this->assertSame(3, (int) $updated->qty_ordered);
        $this->assertSame('piece', (string) $updated->unit_snapshot);

        $service->bulkUpdateItemsInDraft('user-1', 'air-1', [[
            'id' => (string) $created->id,
            'description_snapshot' => 'Bulk-saved description',
            'qty_ordered' => 4,
            'unit_snapshot' => 'box',
            'acquisition_cost' => 2400.00,
        ]]);

        $this->assertDatabaseHas('air_items', [
            'id' => $created->id,
            'description_snapshot' => 'Bulk-saved description',
            'qty_ordered' => 4,
            'unit_snapshot' => 'box',
        ]);

        $service->removeItemFromDraft('user-1', 'air-1', (string) $created->id);

        $this->assertDatabaseMissing('air_items', [
            'id' => $created->id,
        ]);
    }

    public function test_air_item_service_blocks_changes_when_air_is_no_longer_draft(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-submitted',
            'po_number' => 'PO-2026-021',
            'po_date' => '2026-03-21',
            'air_date' => '2026-03-21',
            'supplier_name' => 'Acme Trading',
            'status' => 'submitted',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Laptop',
            'description' => 'Portable computer',
            'base_unit' => 'piece',
            'item_identification' => 'ITM-001',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'is_semi_expendable' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $service = new AirItemService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            $audit,
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Only draft AIR records can edit item rows in this migration slice.');

        $service->addItemToDraft('user-1', 'air-submitted', [
            'item_id' => 'item-1',
            'qty_ordered' => 1,
            'unit_snapshot' => 'piece',
            'acquisition_cost' => 1500,
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('primary_department_id')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->boolean('is_active')->default(true);
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
            $table->string('major_sub_account_group', 255)->nullable();
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
            $table->uuid('parent_air_id')->nullable();
            $table->unsignedInteger('continuation_no')->default(1);
            $table->string('po_number', 255);
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
            $table->string('status', 50)->default('draft');
            $table->date('date_received')->nullable();
            $table->string('received_completeness', 50)->nullable();
            $table->text('received_notes')->nullable();
            $table->date('date_inspected')->nullable();
            $table->boolean('inspection_verified')->nullable();
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
            $table->decimal('acquisition_cost', 12, 2)->nullable();
            $table->unsignedInteger('qty_ordered')->nullable();
            $table->unsignedInteger('qty_delivered')->nullable();
            $table->unsignedInteger('qty_accepted')->nullable();
            $table->string('tracking_type_snapshot', 50)->nullable();
            $table->boolean('requires_serial_snapshot')->default(false);
            $table->boolean('is_semi_expendable_snapshot')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }
}
