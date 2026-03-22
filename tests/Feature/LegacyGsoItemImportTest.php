<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoItemImportTest extends TestCase
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

    public function test_it_imports_items_and_unit_conversions_with_preserved_ids(): void
    {
        DB::table('asset_categories')->insert([
            [
                'id' => 'asset-1',
                'asset_code' => '10604010',
                'asset_name' => 'Office Equipment',
                'created_at' => '2026-01-20 09:00:00',
                'updated_at' => '2026-01-20 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'asset-2',
                'asset_code' => '10605010',
                'asset_name' => 'ICT Equipment',
                'created_at' => '2026-01-20 09:05:00',
                'updated_at' => '2026-01-20 09:05:00',
                'deleted_at' => null,
            ],
        ]);

        DB::connection('gso_legacy')->table('items')->insert([
            [
                'id' => 'item-1',
                'asset_id' => 'asset-1',
                'item_name' => 'Laptop Computer',
                'description' => 'Primary issue unit',
                'base_unit' => 'piece',
                'item_identification' => 'LT-001',
                'major_sub_account_group' => 'ICT Assets',
                'tracking_type' => 'property',
                'requires_serial' => true,
                'is_semi_expendable' => false,
                'is_selected' => true,
                'created_at' => '2026-02-01 08:00:00',
                'updated_at' => '2026-02-01 08:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'item-2',
                'asset_id' => 'asset-2',
                'item_name' => 'Printer Ink',
                'description' => 'Archived consumable',
                'base_unit' => 'bottle',
                'item_identification' => 'INK-002',
                'major_sub_account_group' => 'Supplies',
                'tracking_type' => 'consumable',
                'requires_serial' => false,
                'is_semi_expendable' => true,
                'is_selected' => false,
                'created_at' => '2026-02-01 08:05:00',
                'updated_at' => '2026-02-01 08:05:00',
                'deleted_at' => '2026-02-10 09:00:00',
            ],
        ]);

        DB::connection('gso_legacy')->table('item_unit_conversions')->insert([
            [
                'id' => 'conv-1',
                'item_id' => 'item-1',
                'from_unit' => 'box',
                'multiplier' => 10,
                'created_at' => '2026-02-01 09:00:00',
                'updated_at' => '2026-02-01 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'conv-2',
                'item_id' => 'item-2',
                'from_unit' => 'pack',
                'multiplier' => 12,
                'created_at' => '2026-02-01 09:05:00',
                'updated_at' => '2026-02-01 09:05:00',
                'deleted_at' => '2026-02-11 09:00:00',
            ],
        ]);

        DB::table('items')->insert([
            'id' => 'item-1',
            'asset_id' => 'asset-1',
            'item_name' => 'Old Laptop Name',
            'description' => null,
            'base_unit' => null,
            'item_identification' => null,
            'major_sub_account_group' => null,
            'tracking_type' => 'property',
            'requires_serial' => false,
            'is_semi_expendable' => false,
            'is_selected' => false,
            'created_at' => '2026-01-31 08:00:00',
            'updated_at' => '2026-01-31 08:00:00',
            'deleted_at' => null,
        ]);

        $report = (new LegacyReferenceDataImporter())->import([
            'items',
            'item_unit_conversions',
        ]);

        $this->assertFalse($report['summary']['dry_run']);
        $this->assertSame(2, $report['summary']['selected_tables']);
        $this->assertSame(4, $report['summary']['total_rows_read']);
        $this->assertSame(4, $report['summary']['total_rows_written']);

        $this->assertDatabaseHas('items', [
            'id' => 'item-1',
            'asset_id' => 'asset-1',
            'item_name' => 'Laptop Computer',
            'description' => 'Primary issue unit',
            'base_unit' => 'piece',
            'item_identification' => 'LT-001',
            'major_sub_account_group' => 'ICT Assets',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'is_selected' => true,
        ]);

        $this->assertDatabaseHas('items', [
            'id' => 'item-2',
            'asset_id' => 'asset-2',
            'tracking_type' => 'consumable',
            'deleted_at' => '2026-02-10 09:00:00',
        ]);

        $this->assertDatabaseHas('item_unit_conversions', [
            'id' => 'conv-1',
            'item_id' => 'item-1',
            'from_unit' => 'box',
            'multiplier' => 10,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('item_unit_conversions', [
            'id' => 'conv-2',
            'item_id' => 'item-2',
            'from_unit' => 'pack',
            'multiplier' => 12,
            'deleted_at' => '2026-02-11 09:00:00',
        ]);
    }

    public function test_it_rejects_importing_items_when_asset_categories_are_missing_from_target(): void
    {
        DB::connection('gso_legacy')->table('items')->insert([
            'id' => 'item-missing-asset',
            'asset_id' => 'asset-missing',
            'item_name' => 'Missing Asset Item',
            'description' => null,
            'base_unit' => 'piece',
            'item_identification' => null,
            'major_sub_account_group' => null,
            'tracking_type' => 'property',
            'requires_serial' => false,
            'is_semi_expendable' => false,
            'is_selected' => false,
            'created_at' => '2026-02-01 08:00:00',
            'updated_at' => '2026-02-01 08:00:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced asset categories are missing');

        (new LegacyReferenceDataImporter())->import(['items']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('asset_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('asset_code', 50);
            $table->string('asset_name', 255);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_id');
            $table->string('item_name', 255);
            $table->text('description')->nullable();
            $table->string('base_unit', 50)->nullable();
            $table->string('item_identification', 255)->nullable();
            $table->string('major_sub_account_group', 255)->nullable();
            $table->string('tracking_type', 50)->default('property');
            $table->boolean('requires_serial')->default(false);
            $table->boolean('is_semi_expendable')->default(false);
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('item_unit_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->string('from_unit', 50);
            $table->unsignedInteger('multiplier');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createLegacySchema(): void
    {
        Schema::connection('gso_legacy')->create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_id');
            $table->string('item_name', 255);
            $table->text('description')->nullable();
            $table->string('base_unit', 50)->nullable();
            $table->string('item_identification', 255)->nullable();
            $table->string('major_sub_account_group', 255)->nullable();
            $table->string('tracking_type', 50)->default('property');
            $table->boolean('requires_serial')->default(false);
            $table->boolean('is_semi_expendable')->default(false);
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('gso_legacy')->create('item_unit_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->string('from_unit', 50);
            $table->unsignedInteger('multiplier');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
