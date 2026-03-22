<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoAirImportTest extends TestCase
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

    public function test_it_imports_air_records_with_preserved_ids(): void
    {
        DB::table('departments')->insert([
            'id' => 'dept-1',
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

        DB::connection('gso_legacy')->table('airs')->insert([
            [
                'id' => 'air-root',
                'parent_air_id' => null,
                'continuation_no' => 1,
                'po_number' => 'PO-2026-001',
                'po_date' => '2026-03-20',
                'air_number' => 'AIR-2026-0001',
                'air_date' => '2026-03-21',
                'invoice_number' => 'INV-001',
                'invoice_date' => '2026-03-20',
                'supplier_name' => 'Acme Trading',
                'requesting_department_id' => 'dept-1',
                'requesting_department_name_snapshot' => 'General Services Office',
                'requesting_department_code_snapshot' => 'GSO',
                'fund_source_id' => 'fund-1',
                'fund' => 'General Fund',
                'status' => 'submitted',
                'date_received' => null,
                'received_completeness' => null,
                'received_notes' => null,
                'date_inspected' => null,
                'inspection_verified' => null,
                'inspection_notes' => null,
                'inspected_by_name' => 'Juan Dela Cruz',
                'accepted_by_name' => 'Maria Clara',
                'created_by_user_id' => 'legacy-user-1',
                'created_by_name_snapshot' => 'Legacy User',
                'remarks' => 'Imported root AIR',
                'created_at' => '2026-03-21 08:00:00',
                'updated_at' => '2026-03-21 08:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'air-followup',
                'parent_air_id' => 'air-root',
                'continuation_no' => 2,
                'po_number' => 'PO-2026-001',
                'po_date' => '2026-03-20',
                'air_number' => null,
                'air_date' => '2026-03-22',
                'invoice_number' => null,
                'invoice_date' => null,
                'supplier_name' => 'Acme Trading',
                'requesting_department_id' => 'dept-1',
                'requesting_department_name_snapshot' => 'General Services Office',
                'requesting_department_code_snapshot' => 'GSO',
                'fund_source_id' => 'fund-1',
                'fund' => 'General Fund',
                'status' => 'draft',
                'date_received' => null,
                'received_completeness' => null,
                'received_notes' => null,
                'date_inspected' => null,
                'inspection_verified' => null,
                'inspection_notes' => null,
                'inspected_by_name' => 'Juan Dela Cruz',
                'accepted_by_name' => 'Maria Clara',
                'created_by_user_id' => 'legacy-user-1',
                'created_by_name_snapshot' => 'Legacy User',
                'remarks' => 'Imported follow-up AIR',
                'created_at' => '2026-03-22 08:00:00',
                'updated_at' => '2026-03-22 08:00:00',
                'deleted_at' => null,
            ],
        ]);

        $report = (new LegacyReferenceDataImporter())->import(['airs']);

        $this->assertFalse($report['summary']['dry_run']);
        $this->assertSame(1, $report['summary']['selected_tables']);
        $this->assertSame(2, $report['summary']['total_rows_read']);
        $this->assertSame(2, $report['summary']['total_rows_written']);

        $this->assertDatabaseHas('airs', [
            'id' => 'air-root',
            'po_number' => 'PO-2026-001',
            'air_number' => 'AIR-2026-0001',
            'fund_source_id' => 'fund-1',
            'status' => 'submitted',
        ]);

        $this->assertDatabaseHas('airs', [
            'id' => 'air-followup',
            'parent_air_id' => 'air-root',
            'continuation_no' => 2,
            'status' => 'draft',
        ]);
    }

    public function test_it_rejects_air_import_when_departments_are_missing(): void
    {
        DB::connection('gso_legacy')->table('airs')->insert([
            'id' => 'air-missing-dept',
            'parent_air_id' => null,
            'continuation_no' => 1,
            'po_number' => 'PO-2026-404',
            'po_date' => '2026-03-20',
            'air_number' => null,
            'air_date' => '2026-03-21',
            'invoice_number' => null,
            'invoice_date' => null,
            'supplier_name' => 'Missing Department Supplier',
            'requesting_department_id' => 'dept-missing',
            'requesting_department_name_snapshot' => 'Missing Department',
            'requesting_department_code_snapshot' => 'MISS',
            'fund_source_id' => null,
            'fund' => null,
            'status' => 'draft',
            'date_received' => null,
            'received_completeness' => null,
            'received_notes' => null,
            'date_inspected' => null,
            'inspection_verified' => null,
            'inspection_notes' => null,
            'inspected_by_name' => 'Juan Dela Cruz',
            'accepted_by_name' => 'Maria Clara',
            'created_by_user_id' => null,
            'created_by_name_snapshot' => 'Legacy User',
            'remarks' => null,
            'created_at' => '2026-03-21 08:00:00',
            'updated_at' => '2026-03-21 08:00:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced departments are missing');

        (new LegacyReferenceDataImporter())->import(['airs']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
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
    }

    private function createLegacySchema(): void
    {
        $legacy = Schema::connection('gso_legacy');
        $legacy->dropAllTables();

        $legacy->create('airs', function (Blueprint $table) {
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
    }
}
