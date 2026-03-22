<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\InspectionDatatableRowBuilder;
use App\Modules\GSO\Repositories\Eloquent\EloquentInspectionRepository;
use App\Modules\GSO\Services\InspectionService;
use App\Modules\GSO\Support\InspectionStatuses;
use App\Modules\GSO\Support\InventoryConditions;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoInspectionServiceTest extends TestCase
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

    public function test_inspection_service_handles_crud_filters_and_snapshot_defaults(): void
    {
        DB::table('users')->insert([
            [
                'id' => 'actor-1',
                'username' => 'inspector1',
                'email' => 'inspector1@example.com',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'reviewer-1',
                'username' => 'reviewer1',
                'email' => 'reviewer1@example.com',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('items')->insert([
            [
                'id' => 'item-1',
                'item_name' => 'Laptop Computer',
                'item_identification' => 'LT-001',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'item-2',
                'item_name' => 'Document Camera',
                'item_identification' => 'DC-002',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        DB::table('departments')->insert([
            [
                'id' => 'dept-1',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'dept-2',
                'code' => 'ICT',
                'name' => 'ICT Office',
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(4);

        $service = new InspectionService(
            new EloquentInspectionRepository(),
            $audit,
            new InspectionDatatableRowBuilder(),
        );

        $created = $service->create('actor-1', [
            'item_id' => 'item-1',
            'department_id' => 'dept-1',
            'status' => InspectionStatuses::DRAFT,
            'quantity' => 1,
            'condition' => InventoryConditions::GOOD,
            'observed_description' => 'Initial intake',
        ]);

        $this->assertDatabaseHas('inspections', [
            'id' => $created->id,
            'inspector_user_id' => 'actor-1',
            'item_id' => 'item-1',
            'department_id' => 'dept-1',
            'status' => InspectionStatuses::DRAFT,
            'item_name' => 'Laptop Computer',
            'office_department' => 'GSO - General Services Office',
            'quantity' => 1,
            'condition' => InventoryConditions::GOOD,
        ]);

        $updated = $service->update('actor-1', (string) $created->id, [
            'reviewer_user_id' => 'reviewer-1',
            'item_id' => 'item-2',
            'department_id' => 'dept-2',
            'status' => InspectionStatuses::SUBMITTED,
            'item_name' => 'Document Camera (Observed)',
            'office_department' => 'ICT Storeroom',
            'accountable_officer' => 'Maria Clara',
            'po_number' => 'PO-001',
            'dv_number' => 'DV-001',
            'acquisition_date' => '2026-03-15',
            'acquisition_cost' => 12500,
            'quantity' => 2,
            'condition' => InventoryConditions::FAIR,
            'brand' => 'Logitech',
            'model' => 'Presenter',
            'serial_number' => 'SN-100',
            'observed_description' => 'Needs review before acceptance',
            'remarks' => 'Forwarded to reviewer',
        ]);

        $this->assertSame('reviewer-1', $updated->reviewer_user_id);
        $this->assertSame(InspectionStatuses::SUBMITTED, $updated->status);
        $this->assertSame('Document Camera (Observed)', $updated->item_name);
        $this->assertSame('ICT Storeroom', $updated->office_department);
        $this->assertSame('PO-001', $updated->po_number);
        $this->assertSame(InventoryConditions::FAIR, $updated->condition);

        $editPayload = $service->getForEdit((string) $created->id);

        $this->assertSame('Document Camera (Observed)', $editPayload['item_label']);
        $this->assertSame('ICT Storeroom', $editPayload['department_label']);
        $this->assertSame('Submitted', $editPayload['status_text']);
        $this->assertSame('Fair', $editPayload['condition_text']);
        $this->assertSame('Mar 15, 2026', $editPayload['acquisition_date_text']);

        $filteredPayload = $service->datatable([
            'status' => InspectionStatuses::SUBMITTED,
            'department_id' => 'dept-2',
            'search' => 'po-001',
            'archived' => 'active',
        ]);

        $this->assertSame(1, $filteredPayload['total']);
        $this->assertSame('PO-001', $filteredPayload['data'][0]['po_number']);
        $this->assertSame('Maria Clara', $filteredPayload['data'][0]['accountable_officer']);

        $service->delete('actor-1', (string) $created->id);

        $archivedPayload = $service->datatable([
            'archived' => 'archived',
            'search' => 'po-001',
        ]);

        $this->assertSame(1, $archivedPayload['total']);
        $this->assertTrue($archivedPayload['data'][0]['is_archived']);

        $service->restore('actor-1', (string) $created->id);

        $restoredPayload = $service->datatable([
            'archived' => 'active',
            'search' => 'po-001',
        ]);

        $this->assertSame(1, $restoredPayload['total']);
        $this->assertFalse($restoredPayload['data'][0]['is_archived']);
    }

    public function test_inspection_service_requires_po_number_when_status_is_not_draft(): void
    {
        DB::table('users')->insert([
            'id' => 'actor-1',
            'username' => 'inspector1',
            'email' => 'inspector1@example.com',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldNotReceive('record');

        $service = new InspectionService(
            new EloquentInspectionRepository(),
            $audit,
            new InspectionDatatableRowBuilder(),
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('PO number is required once an inspection leaves draft status.');

        $service->create('actor-1', [
            'status' => InspectionStatuses::SUBMITTED,
            'quantity' => 1,
            'condition' => InventoryConditions::GOOD,
        ]);
    }

    private function createSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_name', 255);
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

        Schema::create('inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inspector_user_id');
            $table->uuid('reviewer_user_id')->nullable();
            $table->string('status', 50)->default('draft');
            $table->uuid('department_id')->nullable();
            $table->uuid('item_id')->nullable();
            $table->string('office_department', 255)->nullable();
            $table->string('accountable_officer', 255)->nullable();
            $table->string('dv_number', 120)->nullable();
            $table->string('po_number', 120)->nullable();
            $table->text('observed_description')->nullable();
            $table->string('item_name', 255)->nullable();
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->decimal('acquisition_cost', 15, 2)->nullable();
            $table->date('acquisition_date')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition', 100)->default('good');
            $table->string('drive_folder_id', 120)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inspection_photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inspection_id');
            $table->string('driver', 20)->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
