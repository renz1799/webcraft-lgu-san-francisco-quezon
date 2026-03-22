<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\StockDatatableRowBuilder;
use App\Modules\GSO\Repositories\Eloquent\EloquentStockMovementRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentStockRepository;
use App\Modules\GSO\Services\StockService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoStockServiceTest extends TestCase
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

    public function test_stock_service_handles_datatable_adjustments_ledger_and_card_preview(): void
    {
        DB::table('fund_clusters')->insert([
            'id' => 'cluster-1',
            'code' => 'GF',
            'name' => 'General Fund Cluster',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('fund_sources')->insert([
            [
                'id' => 'fund-1',
                'fund_cluster_id' => 'cluster-1',
                'code' => 'GF',
                'name' => 'General Fund',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'fund-2',
                'fund_cluster_id' => 'cluster-1',
                'code' => 'SEF',
                'name' => 'Special Education Fund',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('items')->insert([
            [
                'id' => 'item-1',
                'item_name' => 'Bond Paper',
                'description' => 'Short bond paper reams',
                'base_unit' => 'ream',
                'item_identification' => 'BP-001',
                'tracking_type' => 'consumable',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'item-2',
                'item_name' => 'Laptop Computer',
                'description' => 'Should not appear in stocks register',
                'base_unit' => 'unit',
                'item_identification' => 'LT-001',
                'tracking_type' => 'property',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('stocks')->insert([
            [
                'id' => 'stock-1',
                'item_id' => 'item-1',
                'fund_source_id' => 'fund-1',
                'on_hand' => 7,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'stock-2',
                'item_id' => 'item-1',
                'fund_source_id' => 'fund-2',
                'on_hand' => 5,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('stock_movements')->insert([
            [
                'id' => 'movement-1',
                'item_id' => 'item-1',
                'fund_source_id' => 'fund-1',
                'movement_type' => 'in',
                'qty' => 10,
                'reference_type' => 'AIR',
                'reference_id' => 'air-1',
                'air_item_id' => 'air-item-1',
                'ris_item_id' => null,
                'occurred_at' => '2026-03-01 08:00:00',
                'created_by_name' => 'Encoder',
                'remarks' => 'Initial receipt',
                'created_at' => '2026-03-01 08:00:00',
                'updated_at' => '2026-03-01 08:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'movement-2',
                'item_id' => 'item-1',
                'fund_source_id' => 'fund-1',
                'movement_type' => 'issue',
                'qty' => 3,
                'reference_type' => 'RIS',
                'reference_id' => 'ris-1',
                'air_item_id' => null,
                'ris_item_id' => 'ris-item-1',
                'occurred_at' => '2026-03-03 09:30:00',
                'created_by_name' => 'Encoder',
                'remarks' => 'Issued to office',
                'created_at' => '2026-03-03 09:30:00',
                'updated_at' => '2026-03-03 09:30:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'movement-3',
                'item_id' => 'item-1',
                'fund_source_id' => 'fund-2',
                'movement_type' => 'in',
                'qty' => 5,
                'reference_type' => 'AIR',
                'reference_id' => 'air-2',
                'air_item_id' => 'air-item-2',
                'ris_item_id' => null,
                'occurred_at' => '2026-03-02 10:15:00',
                'created_by_name' => 'Encoder',
                'remarks' => 'SEF receipt',
                'created_at' => '2026-03-02 10:15:00',
                'updated_at' => '2026-03-02 10:15:00',
                'deleted_at' => null,
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->once();

        $service = new StockService(
            new EloquentStockRepository(),
            new EloquentStockMovementRepository(),
            $audit,
            new StockDatatableRowBuilder(),
        );

        $datatable = $service->datatable([
            'search' => 'bond',
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-05',
            'archived' => 'active',
        ]);

        $this->assertSame(1, $datatable['total']);
        $this->assertSame('Bond Paper', $datatable['data'][0]['item_name']);
        $this->assertSame(12, $datatable['data'][0]['on_hand']);
        $this->assertCount(2, $datatable['data'][0]['funds']);

        $adjustment = $service->adjustManual(
            actorUserId: 'user-1',
            actorName: 'Maria Clara',
            itemId: 'item-1',
            type: 'increase',
            qty: 7,
            fundSourceId: 'fund-1',
            remarks: 'Physical count correction',
        );

        $this->assertSame(14, $adjustment['new_on_hand']);

        $this->assertDatabaseHas('stocks', [
            'id' => 'stock-1',
            'item_id' => 'item-1',
            'fund_source_id' => 'fund-1',
            'on_hand' => 14,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'item_id' => 'item-1',
            'fund_source_id' => 'fund-1',
            'movement_type' => 'adjust_in',
            'qty' => 7,
            'created_by_name' => 'Maria Clara',
        ]);

        $ledger = $service->getLedgerViewData('item-1', [
            'fund_source_id' => 'fund-1',
            'type' => 'adjust',
            'page' => 1,
            'size' => 15,
        ]);

        $this->assertSame(19, $ledger['onHand']);
        $this->assertCount(2, $ledger['availableFunds']);
        $this->assertSame(1, $ledger['movements']->total());
        $this->assertSame('adjust_in', $ledger['movements']->items()[0]->movement_type);

        $card = $service->getCardPrintViewData('item-1', 'fund-1');

        $this->assertSame('Bond Paper', $card['card']['item_name']);
        $this->assertSame(14, $card['card']['current_on_hand']);
        $this->assertCount(3, $card['rows']);
        $this->assertSame(14, $card['rows'][2]['balance_qty']);
        $this->assertSame('RIS: ris-1', $card['rows'][1]['reference']);
    }

    public function test_stock_service_requires_fund_source_when_item_has_multiple_balances(): void
    {
        DB::table('fund_clusters')->insert([
            'id' => 'cluster-1',
            'code' => 'GF',
            'name' => 'General Fund Cluster',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('fund_sources')->insert([
            [
                'id' => 'fund-1',
                'fund_cluster_id' => 'cluster-1',
                'code' => 'GF',
                'name' => 'General Fund',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'fund-2',
                'fund_cluster_id' => 'cluster-1',
                'code' => 'SEF',
                'name' => 'Special Education Fund',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Bond Paper',
            'base_unit' => 'ream',
            'item_identification' => 'BP-001',
            'tracking_type' => 'consumable',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('stocks')->insert([
            [
                'id' => 'stock-1',
                'item_id' => 'item-1',
                'fund_source_id' => 'fund-1',
                'on_hand' => 5,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'stock-2',
                'item_id' => 'item-1',
                'fund_source_id' => 'fund-2',
                'on_hand' => 2,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $service = new StockService(
            new EloquentStockRepository(),
            new EloquentStockMovementRepository(),
            $audit,
            new StockDatatableRowBuilder(),
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Select a fund source when the item has multiple stock balances.');

        $service->adjustManual(
            actorUserId: 'user-1',
            actorName: 'Maria Clara',
            itemId: 'item-1',
            type: 'increase',
            qty: 1,
            fundSourceId: null,
            remarks: null,
        );
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('fund_clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('fund_cluster_id')->nullable();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_name', 255);
            $table->text('description')->nullable();
            $table->string('base_unit', 50)->nullable();
            $table->string('item_identification', 255)->nullable();
            $table->string('tracking_type', 30)->default('property');
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
}
