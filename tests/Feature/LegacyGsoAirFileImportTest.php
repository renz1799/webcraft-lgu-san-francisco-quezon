<?php

namespace Tests\Feature;

use App\Modules\GSO\Support\LegacyReferenceDataImporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class LegacyGsoAirFileImportTest extends TestCase
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

    public function test_it_imports_air_files_with_preserved_ids(): void
    {
        DB::table('airs')->insert([
            'id' => 'air-1',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        DB::connection('gso_legacy')->table('air_files')->insert([
            'id' => 'air-file-1',
            'air_id' => 'air-1',
            'driver' => 'google',
            'path' => 'drive-file-801',
            'type' => 'pdf',
            'is_primary' => true,
            'position' => 1,
            'original_name' => 'delivery-receipt.pdf',
            'mime' => 'application/pdf',
            'size' => 68000,
            'caption' => 'Receipt',
            'created_at' => '2026-03-22 12:00:00',
            'updated_at' => '2026-03-22 12:10:00',
            'deleted_at' => null,
        ]);

        $report = (new LegacyReferenceDataImporter())->import(['air_files']);

        $this->assertSame(1, $report['summary']['total_rows_written']);
        $this->assertDatabaseHas('air_files', [
            'id' => 'air-file-1',
            'air_id' => 'air-1',
            'path' => 'drive-file-801',
            'type' => 'pdf',
            'is_primary' => 1,
        ]);
    }

    public function test_it_rejects_air_file_import_when_air_records_are_missing(): void
    {
        DB::connection('gso_legacy')->table('air_files')->insert([
            'id' => 'air-file-2',
            'air_id' => 'air-missing',
            'driver' => 'google',
            'path' => 'drive-file-802',
            'type' => 'photo',
            'is_primary' => false,
            'position' => 2,
            'original_name' => 'delivery-photo.jpg',
            'mime' => 'image/jpeg',
            'size' => 18000,
            'caption' => null,
            'created_at' => '2026-03-22 12:30:00',
            'updated_at' => '2026-03-22 12:40:00',
            'deleted_at' => null,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('referenced air records are missing');

        (new LegacyReferenceDataImporter())->import(['air_files']);
    }

    private function createTargetSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('airs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('air_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_id');
            $table->string('driver', 20)->default('google');
            $table->string('path')->nullable();
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
    }

    private function createLegacySchema(): void
    {
        $legacy = Schema::connection('gso_legacy');
        $legacy->dropAllTables();

        $legacy->create('air_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('air_id');
            $table->string('driver', 20)->default('google');
            $table->string('path')->nullable();
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
    }
}
