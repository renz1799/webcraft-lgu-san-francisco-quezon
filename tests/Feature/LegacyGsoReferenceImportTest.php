<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoReferenceImportTest extends TestCase
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

    public function test_it_imports_wave_one_reference_tables_with_preserved_ids_and_core_department_mapping(): void
    {
        DB::connection('gso_legacy')->table('asset_types')->insert([
            [
                'id' => '11111111-1111-1111-1111-111111111111',
                'type_code' => 'PPE',
                'type_name' => 'Property, Plant and Equipment',
                'created_at' => '2026-01-19 09:00:00',
                'updated_at' => '2026-01-19 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'type_code' => 'SUP',
                'type_name' => 'Supplies',
                'created_at' => '2026-01-19 09:05:00',
                'updated_at' => '2026-01-19 09:05:00',
                'deleted_at' => '2026-02-01 08:00:00',
            ],
        ]);

        DB::connection('gso_legacy')->table('asset_categories')->insert([
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'asset_type_id' => '11111111-1111-1111-1111-111111111111',
                'asset_code' => 'PPE-01',
                'asset_name' => 'Office Equipment',
                'account_group' => 'Non-current',
                'is_selected' => false,
                'created_at' => '2026-01-20 09:00:00',
                'updated_at' => '2026-01-20 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => '44444444-4444-4444-4444-444444444444',
                'asset_type_id' => '22222222-2222-2222-2222-222222222222',
                'asset_code' => 'SUP-01',
                'asset_name' => 'Office Supplies',
                'account_group' => 'Current',
                'is_selected' => true,
                'created_at' => '2026-01-20 09:05:00',
                'updated_at' => '2026-01-20 09:05:00',
                'deleted_at' => '2026-02-02 08:00:00',
            ],
        ]);

        DB::connection('gso_legacy')->table('departments')->insert([
            [
                'id' => 'dept-1',
                'department_name' => ' General Services Office ',
                'department_code' => ' GSO ',
                'department_abbr' => ' GSO ',
                'created_at' => '2026-01-21 09:00:00',
                'updated_at' => '2026-01-21 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'dept-2',
                'department_name' => 'Budget Office',
                'department_code' => 'BUDG',
                'department_abbr' => 'BO',
                'created_at' => '2026-01-21 10:00:00',
                'updated_at' => '2026-01-21 10:00:00',
                'deleted_at' => '2026-02-03 08:00:00',
            ],
        ]);

        DB::connection('gso_legacy')->table('fund_clusters')->insert([
            [
                'id' => 'cluster-1',
                'code' => '01',
                'name' => 'General Fund',
                'is_active' => true,
                'created_at' => '2026-01-22 09:00:00',
                'updated_at' => '2026-01-22 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'cluster-2',
                'code' => '02',
                'name' => 'Special Education Fund',
                'is_active' => false,
                'created_at' => '2026-01-22 09:05:00',
                'updated_at' => '2026-01-22 09:05:00',
                'deleted_at' => null,
            ],
        ]);

        DB::connection('gso_legacy')->table('fund_sources')->insert([
            [
                'id' => 'source-1',
                'fund_cluster_id' => 'cluster-1',
                'name' => 'General Fund Source',
                'code' => 'GF',
                'is_active' => true,
                'created_at' => '2026-01-23 09:00:00',
                'updated_at' => '2026-01-23 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'source-2',
                'fund_cluster_id' => 'cluster-2',
                'name' => 'Special Education Fund Source',
                'code' => 'SEF',
                'is_active' => false,
                'created_at' => '2026-01-23 09:05:00',
                'updated_at' => '2026-01-23 09:05:00',
                'deleted_at' => '2026-02-04 08:00:00',
            ],
        ]);

        DB::connection('gso_legacy')->table('accountable_officers')->insert([
            [
                'id' => 'officer-1',
                'full_name' => ' Juan   Dela Cruz ',
                'normalized_name' => '',
                'designation' => ' Supply Officer ',
                'office' => ' GSO ',
                'department_id' => 'dept-1',
                'is_active' => true,
                'created_at' => '2026-01-24 09:00:00',
                'updated_at' => '2026-01-24 09:00:00',
                'deleted_at' => null,
            ],
            [
                'id' => 'officer-2',
                'full_name' => 'Maria Clara',
                'normalized_name' => 'maria clara',
                'designation' => null,
                'office' => 'Budget Office',
                'department_id' => 'dept-2',
                'is_active' => false,
                'created_at' => '2026-01-24 09:05:00',
                'updated_at' => '2026-01-24 09:05:00',
                'deleted_at' => '2026-02-05 08:00:00',
            ],
        ]);

        DB::table('asset_types')->insert([
            'id' => '11111111-1111-1111-1111-111111111111',
            'type_code' => 'PPE',
            'type_name' => 'Old Name',
            'created_at' => '2026-01-18 09:00:00',
            'updated_at' => '2026-01-18 09:00:00',
            'deleted_at' => null,
        ]);

        DB::table('departments')->insert([
            'id' => 'dept-1',
            'code' => 'OLD',
            'name' => 'Old Department Name',
            'short_name' => 'OLD',
            'type' => 'division',
            'parent_department_id' => null,
            'head_user_id' => null,
            'is_active' => false,
            'created_at' => '2026-01-18 09:00:00',
            'updated_at' => '2026-01-18 09:00:00',
            'deleted_at' => null,
        ]);

        $report = (new LegacyReferenceDataImporter())->import([
            'asset_types',
            'asset_categories',
            'departments',
            'fund_clusters',
            'fund_sources',
            'accountable_officers',
        ]);

        $this->assertFalse($report['summary']['dry_run']);
        $this->assertSame(6, $report['summary']['selected_tables']);
        $this->assertSame(12, $report['summary']['total_rows_read']);
        $this->assertSame(12, $report['summary']['total_rows_written']);

        $this->assertDatabaseHas('asset_types', [
            'id' => '11111111-1111-1111-1111-111111111111',
            'type_name' => 'Property, Plant and Equipment',
        ]);

        $this->assertDatabaseHas('asset_categories', [
            'id' => '33333333-3333-3333-3333-333333333333',
            'asset_type_id' => '11111111-1111-1111-1111-111111111111',
            'asset_name' => 'Office Equipment',
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => 'dept-1',
            'code' => 'GSO',
            'name' => 'General Services Office',
            'short_name' => 'GSO',
            'type' => 'division',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => 'dept-2',
            'code' => 'BUDG',
            'name' => 'Budget Office',
            'short_name' => 'BO',
            'type' => 'office',
            'is_active' => false,
            'deleted_at' => '2026-02-03 08:00:00',
        ]);

        $this->assertDatabaseHas('fund_clusters', [
            'id' => 'cluster-2',
            'code' => '02',
            'name' => 'Special Education Fund',
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('fund_sources', [
            'id' => 'source-2',
            'fund_cluster_id' => 'cluster-2',
            'code' => 'SEF',
            'deleted_at' => '2026-02-04 08:00:00',
        ]);

        $this->assertDatabaseHas('accountable_officers', [
            'id' => 'officer-1',
            'full_name' => 'Juan Dela Cruz',
            'normalized_name' => 'juan dela cruz',
            'designation' => 'Supply Officer',
            'office' => 'GSO',
            'department_id' => 'dept-1',
        ]);

        $this->assertDatabaseHas('accountable_officers', [
            'id' => 'officer-2',
            'normalized_name' => 'maria clara',
            'department_id' => 'dept-2',
            'deleted_at' => '2026-02-05 08:00:00',
        ]);
    }

    public function test_it_supports_dry_run_without_writing_target_rows(): void
    {
        DB::connection('gso_legacy')->table('asset_types')->insert([
            'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'type_code' => 'PPE',
            'type_name' => 'Property, Plant and Equipment',
            'created_at' => '2026-01-19 09:00:00',
            'updated_at' => '2026-01-19 09:00:00',
            'deleted_at' => null,
        ]);

        $report = (new LegacyReferenceDataImporter())->import(['asset_types'], true);

        $this->assertTrue($report['summary']['dry_run']);
        $this->assertSame(1, $report['summary']['selected_tables']);
        $this->assertSame(1, $report['summary']['total_rows_read']);
        $this->assertSame(0, $report['summary']['total_rows_written']);
        $this->assertDatabaseMissing('asset_types', [
            'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
        ]);
    }

    public function test_it_rejects_importing_fund_sources_when_referenced_fund_clusters_are_missing_from_target(): void
    {
        DB::connection('gso_legacy')->table('fund_sources')->insert([
            'id' => 'source-missing-cluster',
            'fund_cluster_id' => 'cluster-missing',
            'name' => 'Unmapped Source',
            'code' => 'UMS',
            'is_active' => true,
            'created_at' => '2026-01-23 09:00:00',
            'updated_at' => '2026-01-23 09:00:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced fund clusters are missing');

        (new LegacyReferenceDataImporter())->import(['fund_sources']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('asset_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type_code', 50)->unique();
            $table->string('type_name', 255);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asset_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_type_id');
            $table->string('asset_code', 50);
            $table->string('asset_name', 255);
            $table->string('account_group', 255)->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('asset_type_id')
                ->references('id')
                ->on('asset_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('type', 50)->nullable();
            $table->uuid('parent_department_id')->nullable();
            $table->uuid('head_user_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('code', 30)->nullable()->unique();
            $table->uuid('fund_cluster_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fund_cluster_id')
                ->references('id')
                ->on('fund_clusters')
                ->nullOnDelete();
        });

        Schema::create('accountable_officers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('normalized_name')->unique();
            $table->string('designation')->nullable();
            $table->string('office')->nullable();
            $table->uuid('department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->nullOnDelete();
        });
    }

    private function createLegacySchema(): void
    {
        Schema::connection('gso_legacy')->create('asset_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type_code', 50)->unique();
            $table->string('type_name', 255);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('gso_legacy')->create('asset_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_type_id');
            $table->string('asset_code', 50);
            $table->string('asset_name', 255);
            $table->string('account_group', 255)->nullable();
            $table->boolean('is_selected')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('gso_legacy')->create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('department_name', 255);
            $table->string('department_code', 50)->unique();
            $table->string('department_abbr', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('gso_legacy')->create('fund_clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('gso_legacy')->create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('code', 30)->nullable()->unique();
            $table->uuid('fund_cluster_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('gso_legacy')->create('accountable_officers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('normalized_name')->unique();
            $table->string('designation')->nullable();
            $table->string('office')->nullable();
            $table->uuid('department_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
