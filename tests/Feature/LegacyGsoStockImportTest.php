<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoStockImportTest extends TestCase
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

    public function test_it_imports_stock_tables_with_preserved_ids(): void
    {
        DB::table('items')->insert([
            'id' => 'item-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('fund_sources')->insert([
            'id' => 'fund-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::connection('gso_legacy')->table('stocks')->insert([
            'id' => 'stock-1',
            'item_id' => 'item-1',
            'fund_source_id' => 'fund-1',
            'on_hand' => 14,
            'created_at' => '2026-03-21 08:00:00',
            'updated_at' => '2026-03-21 08:00:00',
            'deleted_at' => null,
        ]);

        DB::connection('gso_legacy')->table('stock_movements')->insert([
            'id' => 'movement-1',
            'item_id' => 'item-1',
            'fund_source_id' => 'fund-1',
            'movement_type' => 'adjust_in',
            'qty' => 7,
            'reference_type' => 'MANUAL_ADJUST',
            'reference_id' => null,
            'air_item_id' => null,
            'ris_item_id' => null,
            'occurred_at' => '2026-03-21 08:15:00',
            'created_by_name' => 'Maria Clara',
            'remarks' => 'Physical count correction',
            'created_at' => '2026-03-21 08:15:00',
            'updated_at' => '2026-03-21 08:15:00',
            'deleted_at' => null,
        ]);

        $report = (new LegacyReferenceDataImporter())->import([
            'stocks',
            'stock_movements',
        ]);

        $this->assertFalse($report['summary']['dry_run']);
        $this->assertSame(2, $report['summary']['selected_tables']);
        $this->assertSame(2, $report['summary']['total_rows_read']);
        $this->assertSame(2, $report['summary']['total_rows_written']);

        $this->assertDatabaseHas('stocks', [
            'id' => 'stock-1',
            'item_id' => 'item-1',
            'fund_source_id' => 'fund-1',
            'on_hand' => 14,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'id' => 'movement-1',
            'item_id' => 'item-1',
            'fund_source_id' => 'fund-1',
            'movement_type' => 'adjust_in',
            'created_by_name' => 'Maria Clara',
        ]);
    }

    public function test_it_rejects_stock_import_when_items_are_missing(): void
    {
        DB::connection('gso_legacy')->table('stocks')->insert([
            'id' => 'stock-missing-item',
            'item_id' => 'missing-item',
            'fund_source_id' => null,
            'on_hand' => 1,
            'created_at' => '2026-03-21 08:00:00',
            'updated_at' => '2026-03-21 08:00:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced items are missing');

        (new LegacyReferenceDataImporter())->import(['stocks']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('fund_source_id')->nullable();
            $table->integer('on_hand')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('fund_source_id')->nullable();
            $table->string('movement_type', 30);
            $table->integer('qty');
            $table->string('reference_type', 50)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->uuid('air_item_id')->nullable();
            $table->uuid('ris_item_id')->nullable();
            $table->dateTime('occurred_at');
            $table->string('created_by_name', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createLegacySchema(): void
    {
        $legacy = Schema::connection('gso_legacy');
        $legacy->dropAllTables();

        $legacy->create('stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('fund_source_id')->nullable();
            $table->integer('on_hand')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $legacy->create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->uuid('fund_source_id')->nullable();
            $table->string('movement_type', 30);
            $table->integer('qty');
            $table->string('reference_type', 50)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->uuid('air_item_id')->nullable();
            $table->uuid('ris_item_id')->nullable();
            $table->dateTime('occurred_at');
            $table->string('created_by_name', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
