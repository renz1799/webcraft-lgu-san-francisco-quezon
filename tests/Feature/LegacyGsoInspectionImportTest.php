<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoInspectionImportTest extends TestCase
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

    public function test_it_imports_inspections_with_preserved_ids_and_dependencies(): void
    {
        DB::table('users')->insert([
            ['id' => 'actor-1'],
            ['id' => 'reviewer-1'],
        ]);

        DB::table('items')->insert([
            ['id' => 'item-1', 'item_name' => 'Laptop Computer', 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null],
        ]);

        DB::table('departments')->insert([
            ['id' => 'dept-1', 'code' => 'GSO', 'name' => 'General Services Office', 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null],
        ]);

        DB::connection('gso_legacy')->table('inspections')->insert([
            [
                'id' => 'inspection-1',
                'inspector_user_id' => 'actor-1',
                'reviewer_user_id' => 'reviewer-1',
                'status' => 'submitted',
                'office_department' => 'GSO - General Services Office',
                'accountable_officer' => 'Juan Dela Cruz',
                'dv_number' => 'DV-001',
                'po_number' => 'PO-001',
                'observed_description' => 'Ready for review',
                'item_name' => 'Laptop Computer',
                'brand' => 'Lenovo',
                'model' => 'ThinkPad',
                'serial_number' => 'SN-001',
                'acquisition_cost' => 55000.25,
                'acquisition_date' => '2026-03-10',
                'department_id' => 'dept-1',
                'item_id' => 'item-1',
                'quantity' => 1,
                'condition' => 'good',
                'drive_folder_id' => 'drive-folder-1',
                'remarks' => 'Imported record',
                'created_at' => '2026-03-10 08:00:00',
                'updated_at' => '2026-03-10 08:00:00',
                'deleted_at' => null,
            ],
        ]);

        DB::table('inspections')->insert([
            'id' => 'inspection-1',
            'inspector_user_id' => 'actor-1',
            'reviewer_user_id' => null,
            'status' => 'draft',
            'office_department' => null,
            'accountable_officer' => null,
            'dv_number' => null,
            'po_number' => null,
            'observed_description' => null,
            'item_name' => null,
            'brand' => null,
            'model' => null,
            'serial_number' => null,
            'acquisition_cost' => null,
            'acquisition_date' => null,
            'department_id' => null,
            'item_id' => null,
            'quantity' => 1,
            'condition' => 'good',
            'drive_folder_id' => null,
            'remarks' => null,
            'created_at' => '2026-03-01 08:00:00',
            'updated_at' => '2026-03-01 08:00:00',
            'deleted_at' => null,
        ]);

        $report = (new LegacyReferenceDataImporter())->import(['inspections']);

        $this->assertFalse($report['summary']['dry_run']);
        $this->assertSame(1, $report['summary']['selected_tables']);
        $this->assertSame(1, $report['summary']['total_rows_read']);
        $this->assertSame(1, $report['summary']['total_rows_written']);

        $this->assertDatabaseHas('inspections', [
            'id' => 'inspection-1',
            'reviewer_user_id' => 'reviewer-1',
            'status' => 'submitted',
            'office_department' => 'GSO - General Services Office',
            'po_number' => 'PO-001',
            'dv_number' => 'DV-001',
            'item_id' => 'item-1',
            'department_id' => 'dept-1',
            'drive_folder_id' => 'drive-folder-1',
        ]);
    }

    public function test_it_rejects_importing_inspections_when_users_are_missing_from_target(): void
    {
        DB::connection('gso_legacy')->table('inspections')->insert([
            'id' => 'inspection-missing-user',
            'inspector_user_id' => 'missing-user',
            'reviewer_user_id' => null,
            'status' => 'draft',
            'office_department' => null,
            'accountable_officer' => null,
            'dv_number' => null,
            'po_number' => null,
            'observed_description' => null,
            'item_name' => null,
            'brand' => null,
            'model' => null,
            'serial_number' => null,
            'acquisition_cost' => null,
            'acquisition_date' => null,
            'department_id' => null,
            'item_id' => null,
            'quantity' => 1,
            'condition' => 'good',
            'drive_folder_id' => null,
            'remarks' => null,
            'created_at' => '2026-03-01 08:00:00',
            'updated_at' => '2026-03-01 08:00:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced users are missing');

        (new LegacyReferenceDataImporter())->import(['inspections']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('item_name', 255)->nullable();
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
            $table->uuid('department_id')->nullable();
            $table->uuid('item_id')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition', 100)->default('good');
            $table->string('drive_folder_id', 120)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createLegacySchema(): void
    {
        Schema::connection('gso_legacy')->create('inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inspector_user_id');
            $table->uuid('reviewer_user_id')->nullable();
            $table->string('status', 50)->default('draft');
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
            $table->uuid('department_id')->nullable();
            $table->uuid('item_id')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('condition', 100)->default('good');
            $table->string('drive_folder_id', 120)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
