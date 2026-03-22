<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoAirItemImportTest extends TestCase
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

    public function test_it_imports_air_items_with_preserved_ids(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('items')->insert([
            'id' => 'item-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('gso_legacy')->table('air_items')->insert([
            'id' => 'air-item-1',
            'air_id' => 'air-1',
            'item_id' => 'item-1',
            'stock_no_snapshot' => 'ITM-001',
            'item_name_snapshot' => 'Laptop',
            'description_snapshot' => 'Portable computer',
            'unit_snapshot' => 'piece',
            'acquisition_cost' => 55000.00,
            'qty_ordered' => 2,
            'qty_delivered' => 1,
            'qty_accepted' => 1,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => true,
            'is_semi_expendable_snapshot' => false,
            'remarks' => 'Imported row',
            'created_at' => '2026-03-21 08:00:00',
            'updated_at' => '2026-03-21 08:30:00',
        ]);

        $report = (new LegacyReferenceDataImporter())->import(['air_items']);

        $this->assertSame(1, $report['summary']['total_rows_written']);
        $this->assertDatabaseHas('air_items', [
            'id' => 'air-item-1',
            'air_id' => 'air-1',
            'item_id' => 'item-1',
            'unit_snapshot' => 'piece',
            'qty_ordered' => 2,
        ]);
    }

    public function test_it_rejects_air_item_import_when_air_records_are_missing(): void
    {
        DB::table('items')->insert([
            'id' => 'item-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('gso_legacy')->table('air_items')->insert([
            'id' => 'air-item-1',
            'air_id' => 'air-missing',
            'item_id' => 'item-1',
            'stock_no_snapshot' => 'ITM-001',
            'item_name_snapshot' => 'Laptop',
            'description_snapshot' => 'Portable computer',
            'unit_snapshot' => 'piece',
            'acquisition_cost' => 55000.00,
            'qty_ordered' => 2,
            'qty_delivered' => 1,
            'qty_accepted' => 1,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => true,
            'is_semi_expendable_snapshot' => false,
            'remarks' => null,
            'created_at' => '2026-03-21 08:00:00',
            'updated_at' => '2026-03-21 08:30:00',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced air records are missing');

        (new LegacyReferenceDataImporter())->import(['air_items']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('airs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
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

    private function createLegacySchema(): void
    {
        $legacy = Schema::connection('gso_legacy');
        $legacy->dropAllTables();

        $legacy->create('air_items', function (Blueprint $table) {
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
