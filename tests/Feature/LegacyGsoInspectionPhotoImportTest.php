<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoInspectionPhotoImportTest extends TestCase
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

    public function test_it_imports_inspection_photos_with_preserved_ids(): void
    {
        DB::table('inspections')->insert([
            'id' => 'inspection-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::connection('gso_legacy')->table('inspection_photos')->insert([
            [
                'id' => 'photo-1',
                'inspection_id' => 'inspection-1',
                'driver' => 'google',
                'path' => 'drive-file-1',
                'original_name' => 'inspection-1.jpg',
                'mime' => 'image/jpeg',
                'size' => 12000,
                'caption' => 'Front view',
                'created_at' => '2026-03-21 08:00:00',
                'updated_at' => '2026-03-21 08:00:00',
                'deleted_at' => null,
            ],
        ]);

        $report = (new LegacyReferenceDataImporter())->import(['inspection_photos']);

        $this->assertFalse($report['summary']['dry_run']);
        $this->assertSame(1, $report['summary']['selected_tables']);
        $this->assertSame(1, $report['summary']['total_rows_read']);
        $this->assertSame(1, $report['summary']['total_rows_written']);

        $this->assertDatabaseHas('inspection_photos', [
            'id' => 'photo-1',
            'inspection_id' => 'inspection-1',
            'driver' => 'google',
            'path' => 'drive-file-1',
            'caption' => 'Front view',
        ]);
    }

    public function test_it_rejects_inspection_photo_import_when_inspections_are_missing(): void
    {
        DB::connection('gso_legacy')->table('inspection_photos')->insert([
            'id' => 'photo-missing-inspection',
            'inspection_id' => 'inspection-missing',
            'driver' => 'google',
            'path' => 'drive-file-missing',
            'original_name' => 'missing.jpg',
            'mime' => 'image/jpeg',
            'size' => 1000,
            'caption' => null,
            'created_at' => '2026-03-21 08:00:00',
            'updated_at' => '2026-03-21 08:00:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced inspections are missing');

        (new LegacyReferenceDataImporter())->import(['inspection_photos']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
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

    private function createLegacySchema(): void
    {
        Schema::connection('gso_legacy')->create('inspection_photos', function (Blueprint $table) {
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
