<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskServiceInterface;
use App\Core\Models\Tasks\Task;
use App\Modules\GSO\Builders\Air\AirDatatableRowBuilder;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemUnitRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirRepository;
use App\Modules\GSO\Services\Air\AirInspectionService;
use App\Modules\GSO\Support\Air\AirStatuses;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoAirInspectionServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Carbon::setTestNow(Carbon::parse('2026-03-22 09:30:00'));

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_saves_and_finalizes_air_inspection_workspace_data(): void
    {
        DB::table('users')->insert([
            'id' => 'user-1',
            'username' => 'gso.staff',
            'email' => 'gso.staff@example.com',
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

        DB::table('airs')->insert([
            'id' => 'air-1',
            'po_number' => 'PO-2026-301',
            'po_date' => '2026-03-20',
            'air_number' => 'AIR-2026-0001',
            'air_date' => '2026-03-20',
            'invoice_number' => null,
            'invoice_date' => null,
            'supplier_name' => 'Acme Trading',
            'requesting_department_id' => 'dept-1',
            'requesting_department_name_snapshot' => 'General Services Office',
            'requesting_department_code_snapshot' => 'GSO',
            'fund_source_id' => 'fund-1',
            'fund' => 'General Fund',
            'status' => AirStatuses::SUBMITTED,
            'inspected_by_name' => 'Juan Dela Cruz',
            'accepted_by_name' => 'Maria Clara',
            'created_by_user_id' => 'user-1',
            'created_by_name_snapshot' => 'gso.staff (gso.staff@example.com)',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Laptop Computer',
            'item_identification' => 'ITM-301',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => 'air-item-1',
            'air_id' => 'air-1',
            'item_id' => 'item-1',
            'stock_no_snapshot' => 'ITM-301',
            'item_name_snapshot' => 'Laptop Computer',
            'description_snapshot' => 'Original description',
            'unit_snapshot' => 'unit',
            'qty_ordered' => 2,
            'qty_delivered' => 0,
            'qty_accepted' => 0,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => true,
            'is_semi_expendable_snapshot' => false,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->twice();
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldReceive('findLatestBySubject')->once()->andReturn(new Task([
            'id' => 'task-1',
            'status' => Task::STATUS_PENDING,
        ]));
        $tasks->shouldReceive('changeStatus')->once()->andReturn(new Task([
            'id' => 'task-1',
            'status' => Task::STATUS_DONE,
        ]));

        $service = new AirInspectionService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            $audit,
            $tasks,
            new AirDatatableRowBuilder(),
        );

        $saved = $service->saveInspection('user-1', 'air-1', [
            'invoice_number' => 'INV-301',
            'invoice_date' => '2026-03-21',
            'date_received' => '2026-03-22',
            'received_completeness' => 'complete',
            'received_notes' => 'All units delivered.',
            'items' => [[
                'id' => 'air-item-1',
                'description_snapshot' => 'Verified description',
                'qty_delivered' => 2,
                'qty_accepted' => 2,
                'remarks' => 'Ready for encoding',
            ]],
        ]);

        $this->assertSame('In Progress', $saved['air']['status_text']);
        $this->assertSame(2, $saved['items'][0]['qty_accepted']);

        $this->assertDatabaseHas('airs', [
            'id' => 'air-1',
            'status' => AirStatuses::IN_PROGRESS,
            'invoice_number' => 'INV-301',
            'date_received' => '2026-03-22 00:00:00',
        ]);

        DB::table('air_item_units')->insert([
            [
                'id' => 'unit-1',
                'air_item_id' => 'air-item-1',
                'inventory_item_id' => null,
                'brand' => 'Lenovo',
                'model' => 'ThinkPad',
                'serial_number' => 'SN-301-A',
                'property_number' => null,
                'condition_status' => 'good',
                'condition_notes' => null,
                'drive_folder_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'unit-2',
                'air_item_id' => 'air-item-1',
                'inventory_item_id' => null,
                'brand' => 'Lenovo',
                'model' => 'ThinkPad',
                'serial_number' => 'SN-301-B',
                'property_number' => null,
                'condition_status' => 'good',
                'condition_notes' => null,
                'drive_folder_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $finalized = $service->finalizeInspection('user-1', 'air-1');

        $this->assertSame('Inspected', $finalized['air']['status_text']);
        $this->assertSame('Mar 22, 2026', $finalized['air']['date_inspected_text']);
        $this->assertSame('Verified', $finalized['air']['inspection_verified_text']);

        $this->assertDatabaseHas('airs', [
            'id' => 'air-1',
            'status' => AirStatuses::INSPECTED,
            'date_inspected' => '2026-03-22 00:00:00',
            'inspection_verified' => 1,
        ]);
    }

    public function test_it_blocks_finalization_when_unit_rows_do_not_match_accepted_quantity(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-2',
            'po_number' => 'PO-2026-302',
            'status' => AirStatuses::IN_PROGRESS,
            'invoice_number' => 'INV-302',
            'invoice_date' => '2026-03-21',
            'date_received' => '2026-03-22',
            'received_completeness' => 'complete',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'item-2',
            'item_name' => 'Desktop Computer',
            'item_identification' => 'ITM-302',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => 'air-item-2',
            'air_id' => 'air-2',
            'item_id' => 'item-2',
            'stock_no_snapshot' => 'ITM-302',
            'item_name_snapshot' => 'Desktop Computer',
            'description_snapshot' => 'Desktop bundle',
            'unit_snapshot' => 'unit',
            'qty_ordered' => 2,
            'qty_delivered' => 2,
            'qty_accepted' => 2,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => true,
            'is_semi_expendable_snapshot' => false,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('air_item_units')->insert([
            'id' => 'unit-only-one',
            'air_item_id' => 'air-item-2',
            'inventory_item_id' => null,
            'brand' => 'Dell',
            'model' => 'OptiPlex',
            'serial_number' => 'SN-302-A',
            'property_number' => null,
            'condition_status' => 'good',
            'condition_notes' => null,
            'drive_folder_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldNotReceive('findLatestBySubject');
        $tasks->shouldNotReceive('changeStatus');

        $service = new AirInspectionService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            $audit,
            $tasks,
            new AirDatatableRowBuilder(),
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('unit rows must match the accepted quantity');

        $service->finalizeInspection('user-1', 'air-2');
    }

    public function test_it_blocks_saving_when_received_completeness_does_not_match_unresolved_items(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-3',
            'po_number' => 'PO-2026-303',
            'status' => AirStatuses::SUBMITTED,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'item-3',
            'item_name' => 'Printer Ink',
            'item_identification' => 'ITM-303',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => 'air-item-3',
            'air_id' => 'air-3',
            'item_id' => 'item-3',
            'stock_no_snapshot' => 'ITM-303',
            'item_name_snapshot' => 'Printer Ink',
            'description_snapshot' => 'Ink cartridge',
            'unit_snapshot' => 'piece',
            'qty_ordered' => 2,
            'qty_delivered' => 1,
            'qty_accepted' => 0,
            'tracking_type_snapshot' => 'consumable',
            'requires_serial_snapshot' => false,
            'is_semi_expendable_snapshot' => false,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldNotReceive('findLatestBySubject');
        $tasks->shouldNotReceive('changeStatus');

        $service = new AirInspectionService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            $audit,
            $tasks,
            new AirDatatableRowBuilder(),
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Received Completeness must be set to Partial');

        $service->saveInspection('user-1', 'air-3', [
            'invoice_number' => 'INV-303',
            'invoice_date' => '2026-03-22',
            'date_received' => '2026-03-22',
            'received_completeness' => 'complete',
            'items' => [[
                'id' => 'air-item-3',
                'qty_delivered' => 1,
                'qty_accepted' => 0,
            ]],
        ]);
    }

    public function test_it_blocks_finalization_for_single_item_air_until_fully_accepted(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-4',
            'po_number' => 'PO-2026-304',
            'status' => AirStatuses::IN_PROGRESS,
            'invoice_number' => 'INV-304',
            'invoice_date' => '2026-03-22',
            'date_received' => '2026-03-22',
            'received_completeness' => 'partial',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'item-4',
            'item_name' => 'Office Chair',
            'item_identification' => 'ITM-304',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => 'air-item-4',
            'air_id' => 'air-4',
            'item_id' => 'item-4',
            'stock_no_snapshot' => 'ITM-304',
            'item_name_snapshot' => 'Office Chair',
            'description_snapshot' => 'Ergonomic chair',
            'unit_snapshot' => 'unit',
            'qty_ordered' => 1,
            'qty_delivered' => 1,
            'qty_accepted' => 0,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => false,
            'is_semi_expendable_snapshot' => false,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $tasks->shouldNotReceive('findLatestBySubject');
        $tasks->shouldNotReceive('changeStatus');

        $service = new AirInspectionService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            $audit,
            $tasks,
            new AirDatatableRowBuilder(),
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Single-item AIR inspections cannot be finalized until the ordered quantity is fully accepted.'
        );

        $service->finalizeInspection('user-1', 'air-4');
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
    }
}
