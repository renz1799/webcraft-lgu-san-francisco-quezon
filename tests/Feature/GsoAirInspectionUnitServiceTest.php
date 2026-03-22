<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemUnitFileRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemUnitRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemRepository;
use App\Modules\GSO\Services\AssetComponentService;
use App\Modules\GSO\Services\AirInspectionUnitService;
use App\Modules\GSO\Support\AirStatuses;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoAirInspectionUnitServiceTest extends TestCase
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

    public function test_it_manages_air_inspection_unit_rows(): void
    {
        $this->seedBaseAirData('air-1', 'air-item-1', 2);

        DB::table('item_component_templates')->insert([
            'id' => 'template-1',
            'item_id' => 'item-1',
            'line_no' => 1,
            'name' => 'Docking Station',
            'quantity' => 1,
            'unit' => 'piece',
            'component_cost' => 3500,
            'remarks' => 'Standard set',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('inventory_items')->insert([
            'id' => 'inventory-1',
            'item_id' => null,
            'property_number' => 'PROP-401',
            'serial_number' => 'SN-401-A',
            'accountable_officer' => null,
            'accountable_officer_id' => null,
            'status' => 'serviceable',
            'condition' => 'good',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(3);

        $service = new AirInspectionUnitService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            new EloquentAirItemUnitFileRepository(),
            new EloquentInventoryItemRepository(),
            new AssetComponentService(),
            $audit,
        );

        $saved = $service->saveForAirItem('user-1', 'air-1', 'air-item-1', [
            [
                'brand' => 'Lenovo',
                'model' => 'ThinkPad',
                'serial_number' => 'SN-401-A',
                'property_number' => '',
                'condition_status' => 'good',
                'condition_notes' => 'Ready',
                'inventory_item_id' => 'inventory-1',
            ],
            [
                'brand' => 'Lenovo',
                'model' => 'ThinkPad',
                'serial_number' => 'SN-401-B',
                'property_number' => '',
                'condition_status' => 'good',
                'condition_notes' => '',
                'components' => [[
                    'name' => 'Docking Station',
                    'quantity' => 1,
                    'unit' => 'piece',
                    'component_cost' => 3500,
                    'serial_number' => 'DS-401-B',
                    'condition' => 'good',
                    'is_present' => false,
                    'remarks' => 'Pending separate delivery',
                ]],
            ],
        ]);

        $this->assertCount(2, $saved['units']);
        $this->assertSame(0, $saved['air_item']['remaining_unit_slots']);
        $this->assertCount(1, $saved['air_item']['default_components']);
        $this->assertDatabaseHas('air_item_units', [
            'air_item_id' => 'air-item-1',
            'serial_number' => 'SN-401-A',
            'inventory_item_id' => 'inventory-1',
        ]);

        $deletableUnit = collect($saved['units'])
            ->first(fn (array $unit): bool => ($unit['inventory_item_id'] ?? null) === null);

        $this->assertNotNull($deletableUnit);
        $this->assertTrue((bool) ($deletableUnit['has_components'] ?? false));
        $this->assertSame(1, (int) ($deletableUnit['component_count'] ?? 0));
        $this->assertSame(3500.0, (float) ($deletableUnit['component_total_cost'] ?? 0));
        $this->assertFalse((bool) ($deletableUnit['components_complete'] ?? true));
        $this->assertDatabaseHas('air_item_unit_components', [
            'air_item_unit_id' => (string) $deletableUnit['id'],
            'name' => 'Docking Station',
            'serial_number' => 'DS-401-B',
            'is_present' => 0,
        ]);

        $deleted = $service->deleteUnit(
            'user-1',
            'air-1',
            'air-item-1',
            (string) $deletableUnit['id'],
        );

        $this->assertCount(1, $deleted['units']);
        $this->assertDatabaseMissing('air_item_units', [
            'id' => (string) $deletableUnit['id'],
            'deleted_at' => null,
        ]);
        $this->assertDatabaseMissing('air_item_unit_components', [
            'air_item_unit_id' => (string) $deletableUnit['id'],
            'deleted_at' => null,
        ]);
    }

    public function test_it_rejects_units_that_exceed_accepted_quantity(): void
    {
        $this->seedBaseAirData('air-2', 'air-item-2', 1);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $service = new AirInspectionUnitService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            new EloquentAirItemUnitFileRepository(),
            new EloquentInventoryItemRepository(),
            new AssetComponentService(),
            $audit,
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('cannot exceed the accepted quantity');

        $service->saveForAirItem('user-1', 'air-2', 'air-item-2', [
            [
                'brand' => 'Lenovo',
                'model' => 'ThinkPad',
                'serial_number' => 'SN-402-A',
                'condition_status' => 'good',
            ],
            [
                'brand' => 'Lenovo',
                'model' => 'ThinkPad',
                'serial_number' => 'SN-402-B',
                'condition_status' => 'good',
            ],
        ]);
    }

    private function seedBaseAirData(string $airId, string $airItemId, int $acceptedQuantity): void
    {
        DB::table('airs')->insert([
            'id' => $airId,
            'po_number' => 'PO-2026-401',
            'status' => AirStatuses::IN_PROGRESS,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Laptop Computer',
            'description' => 'Inspection row',
            'base_unit' => 'unit',
            'item_identification' => 'ITM-401',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'is_semi_expendable' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => $airItemId,
            'air_id' => $airId,
            'item_id' => 'item-1',
            'stock_no_snapshot' => 'ITM-401',
            'item_name_snapshot' => 'Laptop Computer',
            'description_snapshot' => 'Inspection row',
            'unit_snapshot' => 'unit',
            'qty_ordered' => $acceptedQuantity,
            'qty_delivered' => $acceptedQuantity,
            'qty_accepted' => $acceptedQuantity,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => true,
            'is_semi_expendable_snapshot' => false,
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

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id')->nullable();
            $table->uuid('air_item_unit_id')->nullable();
            $table->uuid('department_id')->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('property_number', 120)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->string('accountable_officer', 255)->nullable();
            $table->uuid('accountable_officer_id')->nullable();
            $table->string('status', 100)->nullable();
            $table->string('condition', 100)->nullable();
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
            $table->string('event_type', 100)->nullable();
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
    }
}
