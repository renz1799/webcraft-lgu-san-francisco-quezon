<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoAirComponentImportTest extends TestCase
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

    public function test_it_imports_air_component_tables_with_preserved_ids(): void
    {
        DB::table('items')->insert([
            'id' => 'item-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_item_units')->insert([
            'id' => 'air-unit-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('inventory_items')->insert([
            'id' => 'inventory-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::connection('gso_legacy')->table('item_component_templates')->insert([
            'id' => 'template-1',
            'item_id' => 'item-1',
            'line_no' => 1,
            'name' => 'Docking Station',
            'quantity' => 1,
            'unit' => 'piece',
            'component_cost' => 3500,
            'remarks' => 'Standard set',
            'created_at' => '2026-03-22 09:00:00',
            'updated_at' => '2026-03-22 09:00:00',
            'deleted_at' => null,
        ]);

        DB::connection('gso_legacy')->table('air_item_unit_components')->insert([
            'id' => 'air-component-1',
            'air_item_unit_id' => 'air-unit-1',
            'line_no' => 1,
            'name' => 'Docking Station',
            'quantity' => 1,
            'unit' => 'piece',
            'component_cost' => 3500,
            'serial_number' => 'DS-001',
            'condition' => 'good',
            'is_present' => true,
            'remarks' => 'Included in delivery',
            'created_at' => '2026-03-22 09:10:00',
            'updated_at' => '2026-03-22 09:10:00',
            'deleted_at' => null,
        ]);

        DB::connection('gso_legacy')->table('inventory_item_components')->insert([
            'id' => 'inventory-component-1',
            'inventory_item_id' => 'inventory-1',
            'line_no' => 1,
            'name' => 'Docking Station',
            'quantity' => 1,
            'unit' => 'piece',
            'component_cost' => 3500,
            'serial_number' => 'DS-001',
            'condition' => 'good',
            'is_present' => true,
            'remarks' => 'Carried over from AIR',
            'created_at' => '2026-03-22 09:20:00',
            'updated_at' => '2026-03-22 09:20:00',
            'deleted_at' => null,
        ]);

        $report = (new LegacyReferenceDataImporter())->import([
            'item_component_templates',
            'air_item_unit_components',
            'inventory_item_components',
        ]);

        $this->assertSame(3, $report['summary']['total_rows_written']);
        $this->assertDatabaseHas('item_component_templates', [
            'id' => 'template-1',
            'item_id' => 'item-1',
            'name' => 'Docking Station',
        ]);
        $this->assertDatabaseHas('air_item_unit_components', [
            'id' => 'air-component-1',
            'air_item_unit_id' => 'air-unit-1',
            'serial_number' => 'DS-001',
        ]);
        $this->assertDatabaseHas('inventory_item_components', [
            'id' => 'inventory-component-1',
            'inventory_item_id' => 'inventory-1',
            'remarks' => 'Carried over from AIR',
        ]);
    }

    public function test_it_rejects_air_component_import_when_unit_rows_are_missing(): void
    {
        DB::connection('gso_legacy')->table('air_item_unit_components')->insert([
            'id' => 'air-component-missing',
            'air_item_unit_id' => 'air-unit-missing',
            'line_no' => 1,
            'name' => 'Battery Pack',
            'quantity' => 1,
            'unit' => 'piece',
            'component_cost' => 1500,
            'serial_number' => null,
            'condition' => 'good',
            'is_present' => true,
            'remarks' => null,
            'created_at' => '2026-03-22 09:30:00',
            'updated_at' => '2026-03-22 09:30:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced air item units are missing');

        (new LegacyReferenceDataImporter())->import(['air_item_unit_components']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('air_item_units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
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

        Schema::create('inventory_item_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
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
    }

    private function createLegacySchema(): void
    {
        $legacy = Schema::connection('gso_legacy');
        $legacy->dropAllTables();

        $legacy->create('item_component_templates', function (Blueprint $table) {
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

        $legacy->create('air_item_unit_components', function (Blueprint $table) {
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

        $legacy->create('inventory_item_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
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
    }
}
