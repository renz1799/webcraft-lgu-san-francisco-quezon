<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\AirDatatableRowBuilder;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirRepository;
use App\Modules\GSO\Services\AirService;
use App\Modules\GSO\Support\AirStatuses;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoAirServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Carbon::setTestNow(Carbon::parse('2026-03-21 09:00:00'));

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Mockery::close();

        parent::tearDown();
    }

    public function test_air_service_handles_draft_lifecycle_and_filters(): void
    {
        DB::table('users')->insert([
            'id' => 'user-1',
            'primary_department_id' => 'dept-1',
            'username' => 'gso.admin',
            'email' => 'gso.admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('departments')->insert([
            [
                'id' => 'dept-1',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'dept-2',
                'code' => 'BUDG',
                'name' => 'Budget Office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('fund_sources')->insert([
            [
                'id' => 'fund-gf',
                'code' => 'GF',
                'name' => 'General Fund',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'fund-sef',
                'code' => 'SEF',
                'name' => 'Special Education Fund',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(5);

        $service = new AirService(
            new EloquentAirRepository(),
            $audit,
            new AirDatatableRowBuilder(),
        );

        $created = $service->createBlankDraft('user-1');

        $this->assertDatabaseHas('airs', [
            'id' => $created->id,
            'status' => AirStatuses::DRAFT,
            'requesting_department_id' => 'dept-1',
            'fund_source_id' => 'fund-gf',
            'supplier_name' => 'TBD',
        ]);

        $updated = $service->updateDraft('user-1', (string) $created->id, [
            'po_number' => 'PO-2026-001',
            'po_date' => '2026-03-21',
            'air_number' => '',
            'air_date' => '2026-03-21',
            'invoice_number' => 'INV-100',
            'invoice_date' => '2026-03-20',
            'supplier_name' => 'Acme Trading',
            'requesting_department_id' => 'dept-2',
            'fund_source_id' => 'fund-sef',
            'inspected_by_name' => 'Juan Dela Cruz',
            'accepted_by_name' => 'Maria Clara',
            'remarks' => 'Ready for submission',
        ]);

        $this->assertSame('PO-2026-001', $updated->po_number);
        $this->assertSame('dept-2', $updated->requesting_department_id);
        $this->assertSame('Budget Office', $updated->requesting_department_name_snapshot);
        $this->assertSame('fund-sef', $updated->fund_source_id);

        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Laptop',
            'description' => 'Portable computer',
            'base_unit' => 'unit',
            'item_identification' => 'ITM-001',
            'tracking_type' => 'property',
            'requires_serial' => true,
            'is_semi_expendable' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('item_unit_conversions')->insert([
            'id' => 'conv-1',
            'item_id' => 'item-1',
            'from_unit' => 'box',
            'multiplier' => 5,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => 'air-item-1',
            'air_id' => $created->id,
            'item_id' => 'item-1',
            'stock_no_snapshot' => 'ITM-001',
            'item_name_snapshot' => 'Laptop',
            'description_snapshot' => 'Portable computer',
            'unit_snapshot' => 'unit',
            'acquisition_cost' => 55000,
            'qty_ordered' => 2,
            'qty_delivered' => 0,
            'qty_accepted' => 0,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => true,
            'is_semi_expendable_snapshot' => false,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $filtered = $service->datatable([
            'search' => 'Acme',
            'department_id' => 'dept-2',
            'status' => AirStatuses::DRAFT,
            'archived' => 'active',
        ]);

        $this->assertSame(1, $filtered['total']);
        $this->assertSame('Acme Trading', $filtered['data'][0]['supplier_name']);
        $this->assertSame('BUDG - Budget Office', $filtered['data'][0]['department_label']);

        DB::table('airs')
            ->where('id', $created->id)
            ->update([
                'received_completeness' => 'complete',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $legacyStyleFiltered = $service->datatable([
            'department' => 'Budget',
            'received_completeness' => 'complete',
            'status' => AirStatuses::DRAFT,
            'archived' => 'active',
        ]);

        $this->assertSame(1, $legacyStyleFiltered['total']);
        $this->assertSame('complete', $legacyStyleFiltered['data'][0]['received_completeness']);
        $this->assertSame('BUDG - Budget Office', $legacyStyleFiltered['data'][0]['department_label']);

        $submitted = $service->submitDraft('user-1', (string) $created->id);

        $this->assertSame(AirStatuses::SUBMITTED, $submitted->status);
        $this->assertStringStartsWith('AIR-2026-', (string) $submitted->air_number);

        $service->delete('user-1', (string) $created->id);

        $archived = $service->datatable([
            'search' => 'PO-2026-001',
            'archived' => 'archived',
        ]);

        $this->assertSame(1, $archived['total']);
        $this->assertTrue($archived['data'][0]['is_archived']);

        $service->restore('user-1', (string) $created->id);

        $restored = $service->datatable([
            'search' => 'PO-2026-001',
            'archived' => 'active',
            'status' => AirStatuses::SUBMITTED,
        ]);

        $this->assertSame(1, $restored['total']);
        $this->assertFalse($restored['data'][0]['is_archived']);
        $this->assertSame('Submitted', $restored['data'][0]['status_text']);
    }

    public function test_air_service_requires_real_po_number_before_submit(): void
    {
        DB::table('users')->insert([
            'id' => 'user-1',
            'primary_department_id' => null,
            'username' => 'gso.admin',
            'email' => 'gso.admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('departments')->insert([
            'id' => 'dept-1',
            'code' => 'GSO',
            'name' => 'General Services Office',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('fund_sources')->insert([
            'id' => 'fund-gf',
            'code' => 'GF',
            'name' => 'General Fund',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->once();

        $service = new AirService(
            new EloquentAirRepository(),
            $audit,
            new AirDatatableRowBuilder(),
        );

        $created = $service->createBlankDraft('user-1');

        DB::table('items')->insert([
            'id' => 'item-1',
            'item_name' => 'Office Chair',
            'description' => 'Ergonomic chair',
            'base_unit' => 'piece',
            'item_identification' => 'FUR-001',
            'tracking_type' => 'property',
            'requires_serial' => false,
            'is_semi_expendable' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('air_items')->insert([
            'id' => 'air-item-1',
            'air_id' => $created->id,
            'item_id' => 'item-1',
            'stock_no_snapshot' => 'FUR-001',
            'item_name_snapshot' => 'Office Chair',
            'description_snapshot' => 'Ergonomic chair',
            'unit_snapshot' => 'piece',
            'acquisition_cost' => 4200,
            'qty_ordered' => 1,
            'qty_delivered' => 0,
            'qty_accepted' => 0,
            'tracking_type_snapshot' => 'property',
            'requires_serial_snapshot' => false,
            'is_semi_expendable_snapshot' => false,
            'remarks' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Replace the placeholder PO number before submitting this AIR.');

        $service->submitDraft('user-1', (string) $created->id);
    }

    public function test_air_service_requires_at_least_one_item_before_submit(): void
    {
        DB::table('users')->insert([
            'id' => 'user-1',
            'primary_department_id' => null,
            'username' => 'gso.admin',
            'email' => 'gso.admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('departments')->insert([
            'id' => 'dept-1',
            'code' => 'GSO',
            'name' => 'General Services Office',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('fund_sources')->insert([
            'id' => 'fund-gf',
            'code' => 'GF',
            'name' => 'General Fund',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(2);

        $service = new AirService(
            new EloquentAirRepository(),
            $audit,
            new AirDatatableRowBuilder(),
        );

        $created = $service->createBlankDraft('user-1');
        $service->updateDraft('user-1', (string) $created->id, [
            'po_number' => 'PO-2026-010',
            'po_date' => '2026-03-21',
            'air_date' => '2026-03-21',
            'supplier_name' => 'Acme Trading',
            'requesting_department_id' => 'dept-1',
            'fund_source_id' => 'fund-gf',
            'inspected_by_name' => 'Juan Dela Cruz',
            'accepted_by_name' => 'Maria Clara',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Add at least one item before submitting this AIR.');

        $service->submitDraft('user-1', (string) $created->id);
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('primary_department_id')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_id')->nullable();
            $table->string('item_name', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('base_unit', 50)->nullable();
            $table->string('item_identification', 255)->nullable();
            $table->string('major_sub_account_group', 255)->nullable();
            $table->string('tracking_type', 50)->nullable();
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
            $table->unsignedInteger('multiplier')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('airs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_air_id')->nullable();
            $table->unsignedInteger('continuation_no')->default(1);
            $table->string('po_number', 255);
            $table->date('po_date')->nullable();
            $table->string('air_number', 255)->nullable();
            $table->date('air_date')->nullable();
            $table->string('invoice_number', 255)->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('supplier_name', 255)->nullable();
            $table->uuid('requesting_department_id')->nullable();
            $table->string('requesting_department_name_snapshot', 255)->nullable();
            $table->string('requesting_department_code_snapshot', 100)->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->string('fund', 255)->nullable();
            $table->string('status', 50)->default('draft');
            $table->date('date_received')->nullable();
            $table->string('received_completeness', 50)->nullable();
            $table->text('received_notes')->nullable();
            $table->date('date_inspected')->nullable();
            $table->boolean('inspection_verified')->nullable();
            $table->text('inspection_notes')->nullable();
            $table->string('inspected_by_name', 255)->nullable();
            $table->string('accepted_by_name', 255)->nullable();
            $table->uuid('created_by_user_id')->nullable();
            $table->string('created_by_name_snapshot', 255)->nullable();
            $table->text('remarks')->nullable();
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
