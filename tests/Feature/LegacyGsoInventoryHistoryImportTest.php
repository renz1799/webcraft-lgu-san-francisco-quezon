<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoInventoryHistoryImportTest extends TestCase
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

    public function test_it_imports_inventory_history_tables_with_preserved_ids(): void
    {
        DB::table('inventory_items')->insert([
            'id' => 'inventory-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('departments')->insert([
            'id' => 'dept-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::table('users')->insert([
            'id' => 'user-1',
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

        DB::connection('gso_legacy')->table('inventory_item_files')->insert([
            'id' => 'file-1',
            'inventory_item_id' => 'inventory-1',
            'driver' => 'google',
            'path' => 'drive-file-1',
            'type' => 'photo',
            'is_primary' => true,
            'position' => 1,
            'original_name' => 'front-view.jpg',
            'mime' => 'image/jpeg',
            'size' => 12000,
            'caption' => 'Front view',
            'created_at' => '2026-03-21 08:00:00',
            'updated_at' => '2026-03-21 08:00:00',
            'deleted_at' => null,
        ]);

        DB::connection('gso_legacy')->table('inventory_item_events')->insert([
            'id' => 'event-1',
            'inventory_item_id' => 'inventory-1',
            'department_id' => 'dept-1',
            'performed_by_user_id' => 'user-1',
            'event_type' => 'issued',
            'event_date' => '2026-03-21 08:30:00',
            'qty_in' => 0,
            'qty_out' => 1,
            'amount_snapshot' => 55000.25,
            'unit_snapshot' => 'unit',
            'office_snapshot' => 'GSO - General Services Office',
            'officer_snapshot' => 'Maria Clara',
            'status' => 'serviceable',
            'condition' => 'good',
            'person_accountable' => 'Maria Clara',
            'notes' => 'Issued to office',
            'reference_type' => 'PAR',
            'reference_no' => 'PAR-2026-001',
            'reference_id' => null,
            'fund_source_id' => 'fund-1',
            'created_at' => '2026-03-21 08:30:00',
            'updated_at' => '2026-03-21 08:30:00',
            'deleted_at' => null,
        ]);

        DB::connection('gso_legacy')->table('inventory_item_event_files')->insert([
            'id' => 'event-file-1',
            'inventory_item_event_id' => 'event-1',
            'disk' => 'google',
            'path' => 'events/event-file-1.pdf',
            'drive_file_id' => 'drive-event-file-1',
            'drive_web_view_link' => 'https://example.test/view/event-file-1',
            'original_name' => 'par-scan.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 4096,
            'created_at' => '2026-03-21 08:45:00',
            'updated_at' => '2026-03-21 08:45:00',
            'deleted_at' => null,
        ]);

        $report = (new LegacyReferenceDataImporter())->import([
            'inventory_item_files',
            'inventory_item_events',
            'inventory_item_event_files',
        ]);

        $this->assertFalse($report['summary']['dry_run']);
        $this->assertSame(3, $report['summary']['selected_tables']);
        $this->assertSame(3, $report['summary']['total_rows_read']);
        $this->assertSame(3, $report['summary']['total_rows_written']);

        $this->assertDatabaseHas('inventory_item_files', [
            'id' => 'file-1',
            'inventory_item_id' => 'inventory-1',
            'path' => 'drive-file-1',
            'caption' => 'Front view',
        ]);

        $this->assertDatabaseHas('inventory_item_events', [
            'id' => 'event-1',
            'inventory_item_id' => 'inventory-1',
            'performed_by_user_id' => 'user-1',
            'reference_no' => 'PAR-2026-001',
        ]);

        $this->assertDatabaseHas('inventory_item_event_files', [
            'id' => 'event-file-1',
            'inventory_item_event_id' => 'event-1',
            'drive_file_id' => 'drive-event-file-1',
        ]);
    }

    public function test_it_rejects_inventory_item_event_import_when_users_are_missing(): void
    {
        DB::table('inventory_items')->insert([
            'id' => 'inventory-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::connection('gso_legacy')->table('inventory_item_events')->insert([
            'id' => 'event-missing-user',
            'inventory_item_id' => 'inventory-1',
            'department_id' => null,
            'performed_by_user_id' => 'missing-user',
            'event_type' => 'issued',
            'event_date' => '2026-03-21 08:30:00',
            'qty_in' => 0,
            'qty_out' => 1,
            'amount_snapshot' => 1000,
            'unit_snapshot' => 'unit',
            'office_snapshot' => 'GSO',
            'officer_snapshot' => null,
            'status' => 'serviceable',
            'condition' => 'good',
            'person_accountable' => null,
            'notes' => null,
            'reference_type' => null,
            'reference_no' => null,
            'reference_id' => null,
            'fund_source_id' => null,
            'created_at' => '2026-03-21 08:30:00',
            'updated_at' => '2026-03-21 08:30:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced users are missing');

        (new LegacyReferenceDataImporter())->import(['inventory_item_events']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fund_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_item_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
            $table->string('driver', 20)->default('public');
            $table->string('path');
            $table->string('type', 30)->default('photo');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_item_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
            $table->uuid('department_id')->nullable();
            $table->uuid('performed_by_user_id')->nullable();
            $table->string('event_type', 100);
            $table->dateTime('event_date')->nullable();
            $table->unsignedInteger('qty_in')->default(0);
            $table->unsignedInteger('qty_out')->default(0);
            $table->decimal('amount_snapshot', 15, 2)->nullable();
            $table->string('unit_snapshot', 50)->nullable();
            $table->string('office_snapshot', 255)->nullable();
            $table->string('officer_snapshot', 255)->nullable();
            $table->string('status', 100)->nullable();
            $table->string('condition', 100)->nullable();
            $table->string('person_accountable', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->string('reference_no', 120)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_item_event_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_event_id');
            $table->string('disk', 50)->nullable();
            $table->string('path', 255)->nullable();
            $table->string('drive_file_id', 120)->nullable();
            $table->string('drive_web_view_link', 255)->nullable();
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createLegacySchema(): void
    {
        Schema::connection('gso_legacy')->create('inventory_item_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
            $table->string('driver', 20)->default('public');
            $table->string('path');
            $table->string('type', 30)->default('photo');
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('caption')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('gso_legacy')->create('inventory_item_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_id');
            $table->uuid('department_id')->nullable();
            $table->uuid('performed_by_user_id')->nullable();
            $table->string('event_type', 100);
            $table->dateTime('event_date')->nullable();
            $table->unsignedInteger('qty_in')->default(0);
            $table->unsignedInteger('qty_out')->default(0);
            $table->decimal('amount_snapshot', 15, 2)->nullable();
            $table->string('unit_snapshot', 50)->nullable();
            $table->string('office_snapshot', 255)->nullable();
            $table->string('officer_snapshot', 255)->nullable();
            $table->string('status', 100)->nullable();
            $table->string('condition', 100)->nullable();
            $table->string('person_accountable', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->string('reference_no', 120)->nullable();
            $table->uuid('reference_id')->nullable();
            $table->uuid('fund_source_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('gso_legacy')->create('inventory_item_event_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('inventory_item_event_id');
            $table->string('disk', 50)->nullable();
            $table->string('path', 255)->nullable();
            $table->string('drive_file_id', 120)->nullable();
            $table->string('drive_web_view_link', 255)->nullable();
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
