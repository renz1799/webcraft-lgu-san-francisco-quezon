<?php

namespace Tests\Feature;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\AccountableOfficerDatatableRowBuilder;
use App\Modules\GSO\Builders\AssetCategoryDatatableRowBuilder;
use App\Modules\GSO\Builders\AssetTypeDatatableRowBuilder;
use App\Modules\GSO\Builders\DepartmentDatatableRowBuilder;
use App\Modules\GSO\Builders\FundClusterDatatableRowBuilder;
use App\Modules\GSO\Builders\FundSourceDatatableRowBuilder;
use App\Modules\GSO\Repositories\Eloquent\EloquentAccountableOfficerRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAssetCategoryRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAssetTypeRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentDepartmentRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentFundClusterRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentFundSourceRepository;
use App\Modules\GSO\Services\AccountableOfficerService;
use App\Modules\GSO\Services\AssetCategoryService;
use App\Modules\GSO\Services\AssetTypeService;
use App\Modules\GSO\Services\DepartmentService;
use App\Modules\GSO\Services\FundClusterService;
use App\Modules\GSO\Services\FundSourceService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class GsoReferenceDataServiceTest extends TestCase
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

    public function test_asset_type_service_handles_crud_and_archived_filters(): void
    {
        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(4);

        $service = new AssetTypeService(
            new EloquentAssetTypeRepository(),
            $audit,
            new AssetTypeDatatableRowBuilder(),
        );

        $created = $service->create('actor-1', [
            'type_code' => ' PPE ',
            'type_name' => ' Property, Plant and Equipment ',
        ]);

        $this->assertDatabaseHas('asset_types', [
            'id' => $created->id,
            'type_code' => 'PPE',
            'type_name' => 'Property, Plant and Equipment',
        ]);

        $updated = $service->update('actor-1', (string) $created->id, [
            'type_code' => 'PPE',
            'type_name' => 'Capital Assets',
        ]);

        $this->assertSame('Capital Assets', $updated->type_name);

        $activePayload = $service->datatable([
            'search' => 'capital',
            'archived' => 'active',
        ]);

        $this->assertSame(1, $activePayload['total']);
        $this->assertSame('PPE', $activePayload['data'][0]['type_code']);
        $this->assertFalse($activePayload['data'][0]['is_archived']);

        $service->delete('actor-1', (string) $created->id);

        $archivedPayload = $service->datatable([
            'archived' => 'archived',
        ]);

        $this->assertSame(1, $archivedPayload['total']);
        $this->assertTrue($archivedPayload['data'][0]['is_archived']);

        $service->restore('actor-1', (string) $created->id);

        $restoredPayload = $service->datatable([
            'archived' => 'active',
        ]);

        $this->assertSame(1, $restoredPayload['total']);
        $this->assertFalse($restoredPayload['data'][0]['is_archived']);
    }

    public function test_asset_category_service_handles_crud_and_type_filtering(): void
    {
        DB::table('asset_types')->insert([
            [
                'id' => 'type-1',
                'type_code' => 'PPE',
                'type_name' => 'Property, Plant and Equipment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'type-2',
                'type_code' => 'SUP',
                'type_name' => 'Supplies',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(4);

        $service = new AssetCategoryService(
            new EloquentAssetCategoryRepository(),
            $audit,
            new AssetCategoryDatatableRowBuilder(),
        );

        $created = $service->create('actor-1', [
            'asset_type_id' => 'type-1',
            'asset_code' => '10604010',
            'asset_name' => 'Office Equipment',
            'account_group' => 'PPE',
        ]);

        $this->assertDatabaseHas('asset_categories', [
            'id' => $created->id,
            'asset_type_id' => 'type-1',
            'asset_code' => '10604010',
            'asset_name' => 'Office Equipment',
            'account_group' => 'PPE',
        ]);

        $updated = $service->update('actor-1', (string) $created->id, [
            'asset_type_id' => 'type-2',
            'asset_code' => '10604010',
            'asset_name' => 'Office Supplies',
            'account_group' => 'Consumables',
        ]);

        $this->assertSame('type-2', $updated->asset_type_id);
        $this->assertSame('Office Supplies', $updated->asset_name);
        $this->assertSame('Consumables', $updated->account_group);

        $filteredPayload = $service->datatable([
            'asset_type_id' => 'type-2',
            'search' => 'supplies',
            'archived' => 'active',
        ]);

        $this->assertSame(1, $filteredPayload['total']);
        $this->assertSame('Office Supplies', $filteredPayload['data'][0]['asset_name']);
        $this->assertSame('SUP', $filteredPayload['data'][0]['type']['type_code']);

        $service->delete('actor-1', (string) $created->id);

        $archivedPayload = $service->datatable([
            'archived' => 'archived',
        ]);

        $this->assertSame(1, $archivedPayload['total']);
        $this->assertTrue($archivedPayload['data'][0]['is_archived']);

        $service->restore('actor-1', (string) $created->id);

        $restoredPayload = $service->datatable([
            'archived' => 'active',
        ]);

        $this->assertSame(1, $restoredPayload['total']);
        $this->assertFalse($restoredPayload['data'][0]['is_archived']);
    }

    public function test_department_service_handles_crud_filters_and_active_options(): void
    {
        DB::table('departments')->insert([
            [
                'id' => 'dept-existing',
                'code' => 'TREAS',
                'name' => 'Treasury Office',
                'short_name' => 'TO',
                'type' => 'office',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'dept-archived',
                'code' => 'ARCH',
                'name' => 'Archived Office',
                'short_name' => 'AO',
                'type' => 'office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => now(),
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(4);

        $service = new DepartmentService(
            new EloquentDepartmentRepository(),
            $audit,
            new DepartmentDatatableRowBuilder(),
        );

        $created = $service->create('actor-1', [
            'code' => ' GSO ',
            'name' => ' General Services Office ',
            'short_name' => ' GSO ',
            'type' => ' office ',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('departments', [
            'id' => $created->id,
            'code' => 'GSO',
            'name' => 'General Services Office',
            'short_name' => 'GSO',
            'type' => 'office',
            'is_active' => true,
        ]);

        $updated = $service->update('actor-1', (string) $created->id, [
            'code' => 'GSO',
            'name' => 'General Services and Procurement Office',
            'short_name' => 'GSO',
            'type' => 'division',
            'is_active' => true,
        ]);

        $this->assertSame('General Services and Procurement Office', $updated->name);
        $this->assertSame('division', $updated->type);
        $this->assertTrue($updated->is_active);

        $filteredPayload = $service->datatable([
            'search' => 'procurement',
            'archived' => 'active',
        ]);

        $this->assertSame(1, $filteredPayload['total']);
        $this->assertSame('GSO', $filteredPayload['data'][0]['code']);
        $this->assertSame('Active', $filteredPayload['data'][0]['is_active_text']);

        $optionCodes = $service->optionsForSelect()->pluck('code')->all();
        $this->assertSame(['GSO'], $optionCodes);

        $service->delete('actor-1', (string) $created->id);

        $archivedPayload = $service->datatable([
            'archived' => 'archived',
            'search' => 'general services',
        ]);

        $this->assertSame(1, $archivedPayload['total']);
        $this->assertTrue($archivedPayload['data'][0]['is_archived']);

        $service->restore('actor-1', (string) $created->id);

        $restoredPayload = $service->datatable([
            'archived' => 'active',
            'search' => 'general services',
        ]);

        $this->assertSame(1, $restoredPayload['total']);
        $this->assertFalse($restoredPayload['data'][0]['is_archived']);
    }

    public function test_fund_cluster_service_handles_crud_filters_and_active_options(): void
    {
        DB::table('fund_clusters')->insert([
            [
                'id' => 'cluster-existing',
                'code' => '02',
                'name' => 'Special Education Fund',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'cluster-archived',
                'code' => '99',
                'name' => 'Archived Cluster',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => now(),
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(4);

        $service = new FundClusterService(
            new EloquentFundClusterRepository(),
            $audit,
            new FundClusterDatatableRowBuilder(),
        );

        $created = $service->create('actor-1', [
            'code' => ' 01 ',
            'name' => ' General Fund ',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('fund_clusters', [
            'id' => $created->id,
            'code' => '01',
            'name' => 'General Fund',
            'is_active' => true,
        ]);

        $updated = $service->update('actor-1', (string) $created->id, [
            'code' => '01',
            'name' => 'General Fund Proper',
            'is_active' => false,
        ]);

        $this->assertSame('General Fund Proper', $updated->name);
        $this->assertFalse($updated->is_active);

        $filteredPayload = $service->datatable([
            'search' => 'proper',
            'archived' => 'active',
        ]);

        $this->assertSame(1, $filteredPayload['total']);
        $this->assertSame('01', $filteredPayload['data'][0]['code']);
        $this->assertSame('Inactive', $filteredPayload['data'][0]['is_active_text']);

        $this->assertSame([], $service->optionsForSelect()->pluck('code')->all());

        $service->delete('actor-1', (string) $created->id);

        $archivedPayload = $service->datatable([
            'archived' => 'archived',
            'search' => 'general fund',
        ]);

        $this->assertSame(1, $archivedPayload['total']);
        $this->assertTrue($archivedPayload['data'][0]['is_archived']);

        $service->restore('actor-1', (string) $created->id);

        $restoredPayload = $service->datatable([
            'archived' => 'active',
            'search' => 'general fund',
        ]);

        $this->assertSame(1, $restoredPayload['total']);
        $this->assertFalse($restoredPayload['data'][0]['is_archived']);
    }

    public function test_fund_source_service_handles_crud_and_cluster_filtering(): void
    {
        DB::table('fund_clusters')->insert([
            [
                'id' => 'cluster-1',
                'code' => '01',
                'name' => 'General Fund',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'cluster-2',
                'code' => '02',
                'name' => 'Special Education Fund',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(4);

        $service = new FundSourceService(
            new EloquentFundSourceRepository(),
            $audit,
            new FundSourceDatatableRowBuilder(),
        );

        $created = $service->create('actor-1', [
            'fund_cluster_id' => 'cluster-1',
            'code' => ' GF ',
            'name' => ' General Fund ',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('fund_sources', [
            'id' => $created->id,
            'fund_cluster_id' => 'cluster-1',
            'code' => 'GF',
            'name' => 'General Fund',
            'is_active' => true,
        ]);

        $updated = $service->update('actor-1', (string) $created->id, [
            'fund_cluster_id' => 'cluster-2',
            'code' => 'SEF',
            'name' => 'Special Education Fund',
            'is_active' => false,
        ]);

        $this->assertSame('cluster-2', $updated->fund_cluster_id);
        $this->assertSame('SEF', $updated->code);
        $this->assertSame('Special Education Fund', $updated->name);
        $this->assertFalse($updated->is_active);

        $filteredPayload = $service->datatable([
            'fund_cluster_id' => 'cluster-2',
            'search' => 'special education',
            'archived' => 'active',
        ]);

        $this->assertSame(1, $filteredPayload['total']);
        $this->assertSame('SEF', $filteredPayload['data'][0]['code']);
        $this->assertSame('02 - Special Education Fund', $filteredPayload['data'][0]['fund_cluster_label']);
        $this->assertSame('Inactive', $filteredPayload['data'][0]['is_active_text']);

        $service->delete('actor-1', (string) $created->id);

        $archivedPayload = $service->datatable([
            'archived' => 'archived',
            'search' => 'special education',
        ]);

        $this->assertSame(1, $archivedPayload['total']);
        $this->assertTrue($archivedPayload['data'][0]['is_archived']);

        $service->restore('actor-1', (string) $created->id);

        $restoredPayload = $service->datatable([
            'archived' => 'active',
            'search' => 'special education',
        ]);

        $this->assertSame(1, $restoredPayload['total']);
        $this->assertFalse($restoredPayload['data'][0]['is_archived']);
    }

    public function test_accountable_officer_service_handles_crud_filters_suggestions_and_duplicate_names(): void
    {
        DB::table('departments')->insert([
            [
                'id' => 'dept-1',
                'code' => 'GSO',
                'name' => 'General Services Office',
                'short_name' => 'GSO',
                'type' => 'office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 'dept-2',
                'code' => 'ENG',
                'name' => 'Engineering Office',
                'short_name' => 'ENG',
                'type' => 'office',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);

        $audit = Mockery::mock(AuditLogServiceInterface::class);
        $audit->shouldReceive('record')->times(6);

        $service = new AccountableOfficerService(
            new EloquentAccountableOfficerRepository(),
            $audit,
            new AccountableOfficerDatatableRowBuilder(),
        );

        $created = $service->create('actor-1', [
            'full_name' => ' Juan   Dela Cruz ',
            'designation' => ' Supply Officer ',
            'office' => ' GSO ',
            'department_id' => 'dept-1',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('accountable_officers', [
            'id' => $created->id,
            'full_name' => 'Juan Dela Cruz',
            'normalized_name' => 'juan dela cruz',
            'designation' => 'Supply Officer',
            'office' => 'GSO',
            'department_id' => 'dept-1',
            'is_active' => true,
        ]);

        $suggestions = $service->suggest('juan');
        $this->assertCount(1, $suggestions);
        $this->assertSame('Juan Dela Cruz', $suggestions[0]['full_name']);
        $this->assertSame('GSO (General Services Office)', $suggestions[0]['department_label']);

        try {
            $service->create('actor-1', [
                'full_name' => '  JUAN dela cruz  ',
                'designation' => 'Another Role',
            ]);

            $this->fail('Expected duplicate accountable officer validation to be thrown.');
        } catch (ValidationException $exception) {
            $this->assertSame(
                'An accountable officer with this name already exists.',
                $exception->errors()['full_name'][0] ?? null,
            );
        }

        $updated = $service->update('actor-1', (string) $created->id, [
            'full_name' => 'Juan Dela Cruz',
            'designation' => 'City Accountant',
            'office' => 'Accounting Office',
            'department_id' => 'dept-2',
            'is_active' => false,
        ]);

        $this->assertSame('City Accountant', $updated->designation);
        $this->assertSame('Accounting Office', $updated->office);
        $this->assertSame('dept-2', $updated->department_id);
        $this->assertFalse($updated->is_active);

        $filteredPayload = $service->datatable([
            'department_id' => 'dept-2',
            'search' => 'accounting',
            'archived' => 'active',
        ]);

        $this->assertSame(1, $filteredPayload['total']);
        $this->assertSame('Juan Dela Cruz', $filteredPayload['data'][0]['full_name']);
        $this->assertSame('ENG - Engineering Office', $filteredPayload['data'][0]['department_label']);
        $this->assertSame('Inactive', $filteredPayload['data'][0]['is_active_text']);

        $service->delete('actor-1', (string) $created->id);

        $archivedPayload = $service->datatable([
            'archived' => 'archived',
            'search' => 'juan',
        ]);

        $this->assertSame(1, $archivedPayload['total']);
        $this->assertTrue($archivedPayload['data'][0]['is_archived']);

        $service->restore('actor-1', (string) $created->id);

        $restoredPayload = $service->datatable([
            'archived' => 'active',
            'search' => 'juan',
        ]);

        $this->assertSame(1, $restoredPayload['total']);
        $this->assertFalse($restoredPayload['data'][0]['is_archived']);

        $service->delete('actor-1', (string) $created->id);

        $resolved = $service->createOrResolve('actor-1', [
            'full_name' => ' Juan   Dela Cruz ',
            'department_id' => 'dept-1',
        ]);

        $this->assertFalse($resolved['created']);
        $this->assertTrue($resolved['restored']);
        $this->assertTrue($resolved['reused']);
        $this->assertSame('Juan Dela Cruz', $resolved['officer']['full_name']);
        $this->assertSame('dept-2', $resolved['officer']['department_id']);
    }

    private function createSchema(): void
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
}
