<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\ItemDatatableRowBuilder;
use App\Modules\GSO\Repositories\Eloquent\EloquentItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentItemUnitConversionRepository;
use App\Modules\GSO\Services\ItemService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoItemServiceTest extends TestCase
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

    public function test_item_service_handles_crud_filters_and_unit_conversions(): void
    {
        DB::table('asset_categories')->insert([
            [
                'id' => 'asset-1',
                'asset_code' => '10604010',
                'asset_name' => 'Office Equipment',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'asset-2',
                'asset_code' => '10605010',
                'asset_name' => 'ICT Equipment',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(4);

        $service = new ItemService(
            new EloquentItemRepository(),
            new EloquentItemUnitConversionRepository(),
            $audit,
            new ItemDatatableRowBuilder(),
        );

        $created = $service->create('actor-1', [
            'asset_id' => 'asset-1',
            'item_name' => ' Laptop Computer ',
            'description' => ' Procurement pool unit ',
            'base_unit' => ' piece ',
            'item_identification' => ' LT-001 ',
            'major_sub_account_group' => ' ICT Assets ',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'is_semi_expendable' => false,
            'is_selected' => true,
            'unit_conversions' => [
                ['from_unit' => ' box ', 'multiplier' => 10],
                ['from_unit' => ' pack ', 'multiplier' => 5],
            ],
        ]);

        $this->assertDatabaseHas('items', [
            'id' => $created->id,
            'asset_id' => 'asset-1',
            'item_name' => 'Laptop Computer',
            'description' => 'Procurement pool unit',
            'base_unit' => 'piece',
            'item_identification' => 'LT-001',
            'major_sub_account_group' => 'ICT Assets',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'is_semi_expendable' => false,
            'is_selected' => true,
        ]);

        $this->assertDatabaseHas('item_unit_conversions', [
            'item_id' => $created->id,
            'from_unit' => 'box',
            'multiplier' => 10,
            'deleted_at' => null,
        ]);

        $this->assertDatabaseHas('item_unit_conversions', [
            'item_id' => $created->id,
            'from_unit' => 'pack',
            'multiplier' => 5,
            'deleted_at' => null,
        ]);

        $updated = $service->update('actor-1', (string) $created->id, [
            'asset_id' => 'asset-2',
            'item_name' => 'Laptop Computer',
            'description' => 'Ready for issue',
            'base_unit' => 'unit',
            'item_identification' => 'LT-002',
            'major_sub_account_group' => 'Equipment',
            'tracking_type' => 'consumable',
            'requires_serial' => false,
            'is_semi_expendable' => true,
            'is_selected' => false,
            'unit_conversions' => [
                ['from_unit' => 'pack', 'multiplier' => 12],
            ],
        ]);

        $this->assertSame('asset-2', $updated->asset_id);
        $this->assertSame('consumable', $updated->tracking_type);
        $this->assertFalse($updated->requires_serial);
        $this->assertTrue($updated->is_semi_expendable);

        $editPayload = $service->getForEdit((string) $created->id);

        $this->assertSame('10605010 - ICT Equipment', $editPayload['asset_label']);
        $this->assertSame('Consumable', $editPayload['tracking_type_text']);
        $this->assertCount(1, $editPayload['unit_conversions']);
        $this->assertSame('pack', $editPayload['unit_conversions'][0]['from_unit']);
        $this->assertSame(12, $editPayload['unit_conversions'][0]['multiplier']);

        $this->assertDatabaseHas('item_unit_conversions', [
            'item_id' => $created->id,
            'from_unit' => 'box',
        ]);
        $this->assertDatabaseMissing('item_unit_conversions', [
            'item_id' => $created->id,
            'from_unit' => 'box',
            'deleted_at' => null,
        ]);

        $filteredPayload = $service->datatable([
            'asset_id' => 'asset-2',
            'tracking_type' => 'consumable',
            'requires_serial' => '0',
            'is_semi_expendable' => '1',
            'search' => 'laptop',
            'archived' => 'active',
        ]);

        $this->assertSame(1, $filteredPayload['total']);
        $this->assertSame('Laptop Computer', $filteredPayload['data'][0]['item_name']);
        $this->assertSame('10605010 - ICT Equipment', $filteredPayload['data'][0]['asset_label']);
        $this->assertFalse($filteredPayload['data'][0]['requires_serial']);
        $this->assertTrue($filteredPayload['data'][0]['is_semi_expendable']);

        $service->delete('actor-1', (string) $created->id);

        $archivedPayload = $service->datatable([
            'archived' => 'archived',
            'search' => 'laptop',
        ]);

        $this->assertSame(1, $archivedPayload['total']);
        $this->assertTrue($archivedPayload['data'][0]['is_archived']);

        $service->restore('actor-1', (string) $created->id);

        $restoredPayload = $service->datatable([
            'archived' => 'active',
            'search' => 'laptop',
        ]);

        $this->assertSame(1, $restoredPayload['total']);
        $this->assertFalse($restoredPayload['data'][0]['is_archived']);
    }

    public function test_item_service_rejects_consumables_that_require_serial_numbers(): void
    {
        DB::table('asset_categories')->insert([
            'id' => 'asset-1',
            'asset_code' => '10604010',
            'asset_name' => 'Office Equipment',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $service = new ItemService(
            new EloquentItemRepository(),
            new EloquentItemUnitConversionRepository(),
            $audit,
            new ItemDatatableRowBuilder(),
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Consumable items cannot require serial numbers.');

        $service->create('actor-1', [
            'asset_id' => 'asset-1',
            'item_name' => 'Bond Paper',
            'tracking_type' => 'consumable',
            'requires_serial' => true,
        ]);
    }

    private function createSchema(): void
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

            $table->foreign('asset_id')
                ->references('id')
                ->on('asset_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('item_unit_conversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('item_id');
            $table->string('from_unit', 50);
            $table->unsignedInteger('multiplier');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['item_id', 'from_unit']);
            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
}
