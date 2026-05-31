<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\Notifications\NotificationServiceInterface;
use App\Core\Services\Contracts\Notifications\WorkflowNotificationSettingsServiceInterface;
use App\Core\Services\Tasks\Contracts\TaskServiceInterface;
use App\Modules\GSO\Builders\Contracts\Air\AirDatatableRowBuilderInterface;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemUnitRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirRepository;
use App\Modules\GSO\Services\Air\AirInspectionItemComponentService;
use App\Modules\GSO\Services\Air\AirInspectionService;
use App\Modules\GSO\Services\AssetComponentService;
use App\Modules\GSO\Support\Air\AirStatuses;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoAirInspectionItemComponentServiceTest extends TestCase
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

    public function test_it_saves_and_exposes_air_item_components_for_stock_lines(): void
    {
        $this->seedBaseAirData(
            airId: 'air-stock-1',
            airItemId: 'air-item-stock-1',
            trackingType: 'consumable',
            requiresSerial: false,
            isSemiExpendable: false,
            itemName: 'Bond Paper',
            stockNo: 'STK-100',
        );

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->once();

        $service = new AirInspectionItemComponentService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new AssetComponentService(),
            $audit,
        );

        $service->save('actor-1', 'air-stock-1', 'air-item-stock-1', [
            [
                'client_request_id' => '13a8c5fd-4d5d-4bcb-a524-65c38f8e1001',
                'name' => 'Paper Tray',
                'quantity' => 1,
                'unit' => 'piece',
                'component_cost' => 250.50,
                'serial_number' => 'TRAY-100',
                'condition' => 'good',
                'is_present' => true,
                'remarks' => 'Installed',
            ],
            [
                'client_request_id' => '13a8c5fd-4d5d-4bcb-a524-65c38f8e1002',
                'name' => 'Paper Guide',
                'quantity' => 2,
                'unit' => 'piece',
                'component_cost' => 0,
                'serial_number' => '',
                'condition' => 'good',
                'is_present' => false,
                'remarks' => 'Missing on delivery',
            ],
        ]);

        $this->assertDatabaseHas('air_item_components', [
            'air_item_id' => 'air-item-stock-1',
            'client_request_id' => '13a8c5fd-4d5d-4bcb-a524-65c38f8e1001',
            'name' => 'Paper Tray',
            'serial_number' => 'TRAY-100',
        ]);

        $inspectionPayload = $this->makeInspectionService()->getForInspection(
            'air-stock-1',
        );

        $this->assertCount(2, $inspectionPayload['items'][0]['components']);
        $this->assertSame(2, (int) ($inspectionPayload['items'][0]['component_count'] ?? 0));
        $this->assertTrue((bool) ($inspectionPayload['items'][0]['has_components'] ?? false));
        $this->assertSame(
            '13a8c5fd-4d5d-4bcb-a524-65c38f8e1001',
            $inspectionPayload['items'][0]['components'][0]['client_request_id'],
        );
        $this->assertFalse((bool) ($inspectionPayload['items'][0]['components_complete'] ?? true));
    }

    public function test_it_replaces_existing_stock_components_when_given_an_empty_payload(): void
    {
        $this->seedBaseAirData(
            airId: 'air-stock-2',
            airItemId: 'air-item-stock-2',
            trackingType: 'consumable',
            requiresSerial: false,
            isSemiExpendable: false,
            itemName: 'Printer Ink',
            stockNo: 'STK-200',
        );

        DB::table('air_item_components')->insert([
            'id' => 'component-existing-1',
            'air_item_id' => 'air-item-stock-2',
            'client_request_id' => null,
            'line_no' => 1,
            'name' => 'Cartridge',
            'quantity' => 1,
            'unit' => 'piece',
            'component_cost' => 1200,
            'serial_number' => null,
            'condition' => 'good',
            'is_present' => 1,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->once();

        $service = new AirInspectionItemComponentService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new AssetComponentService(),
            $audit,
        );

        $service->save('actor-1', 'air-stock-2', 'air-item-stock-2', []);

        $payload = $this->makeInspectionService()->getForInspection('air-stock-2');
        $this->assertCount(0, $payload['items'][0]['components']);
        $this->assertDatabaseMissing('air_item_components', [
            'id' => 'component-existing-1',
            'deleted_at' => null,
        ]);
    }

    public function test_it_rejects_serialized_lines_for_item_component_sync(): void
    {
        $this->seedBaseAirData(
            airId: 'air-serialized-1',
            airItemId: 'air-item-serialized-1',
            trackingType: 'property',
            requiresSerial: true,
            isSemiExpendable: false,
            itemName: 'Laptop Computer',
            stockNo: 'ITM-500',
        );

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $service = new AirInspectionItemComponentService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new AssetComponentService(),
            $audit,
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'This AIR line stores inspection components on inspection units instead of the AIR item record.',
        );

        $service->save('actor-1', 'air-serialized-1', 'air-item-serialized-1', [
            [
                'client_request_id' => '13a8c5fd-4d5d-4bcb-a524-65c38f8e2001',
                'name' => 'Dock',
                'quantity' => 1,
            ],
        ]);
    }

    private function makeInspectionService(): AirInspectionService
    {
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $tasks = Mockery::mock(TaskServiceInterface::class);
        $notifications = Mockery::mock(NotificationServiceInterface::class);
        $workflowNotifications = Mockery::mock(
            WorkflowNotificationSettingsServiceInterface::class,
        );
        $airRowBuilder = Mockery::mock(AirDatatableRowBuilderInterface::class);

        $airRowBuilder->shouldReceive('build')->andReturnUsing(
            fn ($air) => [
                'id' => (string) $air->id,
                'status' => (string) ($air->status ?? ''),
            ],
        );

        return new AirInspectionService(
            new EloquentAirRepository(),
            new EloquentAirItemRepository(),
            new EloquentAirItemUnitRepository(),
            $audit,
            $tasks,
            $notifications,
            $workflowNotifications,
            $airRowBuilder,
        );
    }

    private function seedBaseAirData(
        string $airId,
        string $airItemId,
        string $trackingType,
        bool $requiresSerial,
        bool $isSemiExpendable,
        string $itemName,
        string $stockNo,
    ): void {
        DB::table('airs')->insert([
            'id' => $airId,
            'po_number' => 'PO-900',
            'air_number' => 'AIR-2026-9001',
            'status' => AirStatuses::IN_PROGRESS,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('items')->insert([
            'id' => 'item-1',
            'asset_id' => null,
            'item_name' => $itemName,
            'description' => 'Inspection item',
            'base_unit' => 'box',
            'item_identification' => $stockNo,
            'tracking_type' => $trackingType,
            'requires_serial' => $requiresSerial,
            'is_semi_expendable' => $isSemiExpendable,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => $airItemId,
            'air_id' => $airId,
            'item_id' => 'item-1',
            'stock_no_snapshot' => $stockNo,
            'item_name_snapshot' => $itemName,
            'description_snapshot' => 'Inspection item',
            'unit_snapshot' => 'box',
            'acquisition_cost' => 0,
            'qty_ordered' => 2,
            'qty_delivered' => 2,
            'qty_accepted' => 2,
            'tracking_type_snapshot' => $trackingType,
            'requires_serial_snapshot' => $requiresSerial,
            'is_semi_expendable_snapshot' => $isSemiExpendable,
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
            $table->string('air_number', 255)->nullable();
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

        Schema::create('air_item_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id');
            $table->string('storage_provider', 50)->default('google');
            $table->string('storage_disk', 50)->nullable();
            $table->string('storage_path');
            $table->string('external_file_id', 255)->nullable();
            $table->string('original_name')->nullable();
            $table->string('stored_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('air_item_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id');
            $table->uuid('client_request_id')->nullable();
            $table->unsignedInteger('line_no')->default(1);
            $table->string('name', 255);
            $table->unsignedInteger('quantity')->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('component_cost', 15, 2)->default(0);
            $table->string('serial_number', 255)->nullable();
            $table->string('condition', 255)->nullable();
            $table->boolean('is_present')->default(true);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
