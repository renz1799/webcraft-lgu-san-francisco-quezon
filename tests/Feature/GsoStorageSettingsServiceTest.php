<?php

namespace Tests\Feature;

use App\Core\Models\AppSetting;
use App\Core\Models\Module;
use App\Core\Services\GoogleDrive\GoogleDriveSettingsProvider;
use App\Modules\GSO\Services\GsoStorageSettingsService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GsoStorageSettingsServiceTest extends TestCase
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

    public function test_new_root_settings_override_legacy_gso_config(): void
    {
        config()->set('services.google_drive.folder_id', 'global-folder');
        config()->set('gso.storage.air_files_folder_id', 'legacy-air-folder');
        config()->set('gso.storage.inspection_photos_folder_id', 'legacy-inspection-folder');

        $module = Module::query()->create([
            'code' => 'GSO',
            'name' => 'General Services Office',
            'description' => 'GSO module',
            'url' => 'https://gso.test',
            'is_active' => true,
        ]);

        AppSetting::query()->create([
            'module_id' => (string) $module->id,
            'key' => 'storage.google_drive',
            'value' => [
                'signed_documents_root_folder_id' => 'app-setting-signed-root',
                'air_inspections_root_folder_id' => 'app-setting-inspection-root',
            ],
        ]);

        $service = $this->makeService();

        $this->assertSame('app-setting-signed-root', $service->airFilesFolderId());
        $this->assertSame('app-setting-inspection-root', $service->inspectionPhotosFolderId());
        $this->assertSame('app-setting-inspection-root', $service->airUnitFilesFolderId());
    }

    public function test_legacy_app_setting_aliases_and_config_fallbacks_still_work(): void
    {
        config()->set('services.google_drive.folder_id', 'global-folder');
        config()->set('gso.storage.inventory_files_folder_id', 'legacy-inventory-folder');

        $module = Module::query()->create([
            'code' => 'GSO',
            'name' => 'General Services Office',
            'description' => 'GSO module',
            'url' => 'https://gso.test',
            'is_active' => true,
        ]);

        AppSetting::query()->create([
            'module_id' => (string) $module->id,
            'key' => 'storage.google_drive',
            'value' => [
                'air_files_folder_id' => 'legacy-stored-air-folder',
            ],
        ]);

        $service = $this->makeService();

        $this->assertSame('legacy-stored-air-folder', $service->airFilesFolderId());
        $this->assertSame('legacy-inventory-folder', $service->inventoryFilesFolderId());
    }

    public function test_missing_app_setting_falls_back_to_config_and_global_drive_default(): void
    {
        config()->set('services.google_drive.folder_id', 'global-folder');
        config()->set('gso.storage.inspection_photos_folder_id', 'legacy-inspection-folder');
        config()->set('gso.storage.inventory_files_folder_id', null);

        Module::query()->create([
            'code' => 'GSO',
            'name' => 'General Services Office',
            'description' => 'GSO module',
            'url' => 'https://gso.test',
            'is_active' => true,
        ]);

        $service = $this->makeService();

        $this->assertSame('legacy-inspection-folder', $service->inspectionPhotosFolderId());
        $this->assertSame('global-folder', $service->inventoryFilesFolderId());
    }

    private function makeService(): GsoStorageSettingsService
    {
        return new GsoStorageSettingsService(new GoogleDriveSettingsProvider());
    }

    private function createSchema(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('app_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('module_id');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['module_id', 'key'], 'app_settings_module_key_unique');
        });
    }
}
