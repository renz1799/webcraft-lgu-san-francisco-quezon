<?php

namespace Tests\Feature;

use App\Modules\GSO\Services\Contracts\InventoryItemFileServiceInterface;
use App\Modules\GSO\Services\InventoryItemPublicAssetService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class GsoPublicInventoryAssetServiceTest extends TestCase
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

    public function test_it_builds_public_asset_payload_using_inventory_item_reference_codes(): void
    {
        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Laptop Computer',
            'item_identification' => 'LT-001',
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

        DB::table('inventory_items')->insert([
            'id' => 'inventory-1',
            'item_id' => 'item-1',
            'department_id' => 'dept-1',
            'fund_source_id' => null,
            'property_number' => 'PROP-001',
            'acquisition_date' => '2026-03-01',
            'acquisition_cost' => 55000.25,
            'description' => 'Dell Latitude Laptop',
            'quantity' => 1,
            'unit' => 'unit',
            'stock_number' => 'STK-001',
            'service_life' => 5,
            'is_ics' => false,
            'accountable_officer' => 'Maria Clara',
            'accountable_officer_id' => null,
            'custody_state' => 'issued',
            'status' => 'serviceable',
            'condition' => 'good',
            'brand' => 'Dell',
            'model' => 'Latitude 5440',
            'serial_number' => 'SN-001',
            'po_number' => 'PO-001',
            'drive_folder_id' => 'drive-folder-1',
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('inventory_item_files')->insert([
            [
                'id' => 'photo-1',
                'inventory_item_id' => 'inventory-1',
                'driver' => 'google',
                'path' => 'drive-photo-1',
                'type' => 'photo',
                'is_primary' => true,
                'position' => 1,
                'original_name' => 'front.jpg',
                'mime' => 'image/jpeg',
                'size' => 12000,
                'caption' => 'Front view',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'manual-1',
                'inventory_item_id' => 'inventory-1',
                'driver' => 'google',
                'path' => 'drive-manual-1',
                'type' => 'pdf',
                'is_primary' => false,
                'position' => 2,
                'original_name' => 'manual.pdf',
                'mime' => 'application/pdf',
                'size' => 22000,
                'caption' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $files = Mockery::mock(InventoryItemFileServiceInterface::class);
        $files->shouldNotReceive('preview');

        $service = new InventoryItemPublicAssetService($files);
        $payload = $service->getPublicAssetPagePayload('prop-001');

        $this->assertSame('gso::inventory-items.public-show', $payload['view']);
        $this->assertSame('PPE', $payload['data']['asset']['type_label']);
        $this->assertSame('Property Number', $payload['data']['asset']['reference_label']);
        $this->assertSame('PROP-001', $payload['data']['asset']['reference_value']);
        $this->assertSame('Dell Latitude Laptop', $payload['data']['asset']['description']);
        $this->assertSame('GSO - General Services Office', $payload['data']['asset']['office']);
        $this->assertSame('Serviceable', $payload['data']['asset']['status']);
        $this->assertSame('Good', $payload['data']['asset']['condition']);
        $this->assertCount(1, $payload['data']['asset']['photos']);
        $this->assertStringContainsString('/gso/assets/PROP-001/files/photo-1/preview', (string) $payload['data']['asset']['primary_photo_url']);
    }

    public function test_it_streams_only_public_photo_files_for_a_resolved_asset(): void
    {
        DB::table('inventory_items')->insert([
            'id' => 'inventory-1',
            'item_id' => null,
            'department_id' => null,
            'fund_source_id' => null,
            'property_number' => 'PROP-001',
            'acquisition_date' => null,
            'acquisition_cost' => null,
            'description' => null,
            'quantity' => 1,
            'unit' => null,
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
        ]);

        DB::table('inventory_item_files')->insert([
            [
                'id' => 'photo-1',
                'inventory_item_id' => 'inventory-1',
                'driver' => 'google',
                'path' => 'drive-photo-1',
                'type' => 'photo',
                'is_primary' => true,
                'position' => 1,
                'original_name' => 'front.jpg',
                'mime' => 'image/jpeg',
                'size' => 12000,
                'caption' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'manual-1',
                'inventory_item_id' => 'inventory-1',
                'driver' => 'google',
                'path' => 'drive-manual-1',
                'type' => 'pdf',
                'is_primary' => false,
                'position' => 2,
                'original_name' => 'manual.pdf',
                'mime' => 'application/pdf',
                'size' => 22000,
                'caption' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $files = Mockery::mock(InventoryItemFileServiceInterface::class);
        $files->shouldReceive('preview')
            ->once()
            ->with('inventory-1', 'photo-1')
            ->andReturn([
                'name' => 'front.jpg',
                'mime' => 'image/jpeg',
                'bytes' => 'jpeg-bytes',
            ]);

        $service = new InventoryItemPublicAssetService($files);

        $preview = $service->streamPublicAssetFile('PROP-001', 'photo-1');
        $this->assertSame('jpeg-bytes', $preview['bytes']);

        $this->expectException(ModelNotFoundException::class);

        $service->streamPublicAssetFile('PROP-001', 'manual-1');
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_name', 255)->nullable();
            $table->string('item_identification', 255)->nullable();
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
    }
}
