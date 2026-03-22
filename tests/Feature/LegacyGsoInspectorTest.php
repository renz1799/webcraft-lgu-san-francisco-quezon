<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyGsoInspector;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyGsoInspectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.connections.gso_legacy', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('gso_legacy');
        DB::reconnect('gso_legacy');

        config()->set('gso.legacy.connection', 'gso_legacy');

        Schema::connection('gso_legacy')->create('asset_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
        });

        Schema::connection('gso_legacy')->create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
        });

        Schema::connection('gso_legacy')->create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
        });

        DB::connection('gso_legacy')->table('asset_types')->insert([
            ['id' => 'asset-type-1', 'name' => 'Furniture'],
            ['id' => 'asset-type-2', 'name' => 'Equipment'],
        ]);

        DB::connection('gso_legacy')->table('items')->insert([
            ['id' => 'item-1', 'name' => 'Office Chair'],
        ]);

        DB::connection('gso_legacy')->table('roles')->insert([
            ['id' => 'role-1', 'name' => 'Administrator'],
        ]);
    }

    public function test_it_inspects_wave_tables_and_shared_tables_from_the_configured_legacy_connection(): void
    {
        $inspector = new LegacyGsoInspector();

        $report = $inspector->inspect(1, ['asset_types', 'items', 'inventory_items', 'roles']);

        $this->assertTrue($report['summary']['connection_reachable']);
        $this->assertSame(3, $report['summary']['selected_tables']);
        $this->assertSame(2, $report['summary']['existing_tables']);
        $this->assertSame(1, $report['summary']['missing_tables']);
        $this->assertSame(1, $report['summary']['selected_shared_tables']);
        $this->assertSame(1, $report['summary']['existing_shared_tables']);
        $this->assertSame(3, $report['summary']['total_rows']);
        $this->assertSame(1, $report['summary']['shared_total_rows']);

        $waveTables = collect($report['tables'])->keyBy('table');

        $this->assertTrue($waveTables['asset_types']['exists']);
        $this->assertSame(2, $waveTables['asset_types']['row_count']);
        $this->assertSame('id', $waveTables['asset_types']['id_column']);

        $this->assertTrue($waveTables['items']['exists']);
        $this->assertFalse($waveTables['inventory_items']['exists']);

        $sharedTables = collect($report['shared_tables'])->keyBy('table');

        $this->assertTrue($sharedTables['roles']['exists']);
        $this->assertSame(1, $sharedTables['roles']['row_count']);
    }
}
