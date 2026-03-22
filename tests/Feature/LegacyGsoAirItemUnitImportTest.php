<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoAirItemUnitImportTest extends TestCase
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

    public function test_it_imports_air_item_units_with_nullable_inventory_links(): void
    {
        DB::table('air_items')->insert([
            'id' => 'air-item-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('gso_legacy')->table('air_item_units')->insert([
            'id' => 'unit-1',
            'air_item_id' => 'air-item-1',
            'inventory_item_id' => null,
            'brand' => 'Lenovo',
            'model' => 'ThinkPad',
            'serial_number' => 'SN-601-A',
            'property_number' => null,
            'condition_status' => 'good',
            'condition_notes' => 'Imported',
            'created_at' => '2026-03-22 08:00:00',
            'updated_at' => '2026-03-22 08:30:00',
            'deleted_at' => null,
        ]);

        $report = (new LegacyReferenceDataImporter())->import(['air_item_units']);

        $this->assertSame(1, $report['summary']['total_rows_written']);
        $this->assertDatabaseHas('air_item_units', [
            'id' => 'unit-1',
            'air_item_id' => 'air-item-1',
            'inventory_item_id' => null,
            'serial_number' => 'SN-601-A',
            'condition_status' => 'good',
        ]);
    }

    public function test_it_rejects_air_item_unit_import_when_inventory_dependencies_are_missing(): void
    {
        DB::table('air_items')->insert([
            'id' => 'air-item-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('gso_legacy')->table('air_item_units')->insert([
            'id' => 'unit-2',
            'air_item_id' => 'air-item-1',
            'inventory_item_id' => 'inventory-missing',
            'brand' => 'Lenovo',
            'model' => 'ThinkPad',
            'serial_number' => 'SN-601-B',
            'property_number' => null,
            'condition_status' => 'good',
            'condition_notes' => null,
            'created_at' => '2026-03-22 09:00:00',
            'updated_at' => '2026-03-22 09:30:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced inventory items are missing');

        (new LegacyReferenceDataImporter())->import(['air_item_units']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('air_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
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

    private function createLegacySchema(): void
    {
        $legacy = Schema::connection('gso_legacy');
        $legacy->dropAllTables();

        $legacy->create('air_item_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_item_id');
            $table->uuid('inventory_item_id')->nullable();
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->string('property_number', 255)->nullable();
            $table->string('condition_status', 100)->nullable();
            $table->text('condition_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
