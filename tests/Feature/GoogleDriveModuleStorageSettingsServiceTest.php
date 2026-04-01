<?php

namespace Tests\Feature;

use App\Core\Models\AppSetting;
use App\Core\Models\Module;
use App\Core\Services\GoogleDrive\GoogleDriveModuleStorageSettingsService;
use App\Core\Services\GoogleDrive\GoogleDriveSettingsProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GoogleDriveModuleStorageSettingsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->createSchema();
        $this->seedConfig();
    }

    public function test_contexts_show_stored_and_fallback_values(): void
    {
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
                'air_files_folder_id' => 'stored-signed-folder',
            ],
        ]);

        $context = $this->makeService()->contexts()->first();

        $this->assertNotNull($context);
        $this->assertSame('GSO Google Drive Storage Plan', $context['title']);

        $fields = collect($context['fields'])->keyBy('key');

        $this->assertSame('stored-signed-folder', $fields['signed_documents_root_folder_id']['effective_value']);
        $this->assertSame('App settings (legacy key: air_files_folder_id)', $fields['signed_documents_root_folder_id']['source']);
        $this->assertSame('legacy-inspection-folder', $fields['air_inspections_root_folder_id']['effective_value']);
        $this->assertSame('Config fallback (gso.storage.inspection_photos_folder_id)', $fields['air_inspections_root_folder_id']['source']);
    }

    public function test_update_normalizes_to_new_root_keys_and_allows_blank_reset_to_fallback(): void
    {
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
                'inventory_files_folder_id' => 'legacy-stored-inventory-folder',
            ],
        ]);

        $service = $this->makeService();

        $stored = $service->updateModuleSettings((string) $module->id, [
            'signed_documents_root_folder_id' => 'new-signed-folder',
            'air_inspections_root_folder_id' => 'new-inspection-root',
            'inventory_items_root_folder_id' => '',
        ]);

        $this->assertSame('new-signed-folder', $stored['signed_documents_root_folder_id']);
        $this->assertSame('new-inspection-root', $stored['air_inspections_root_folder_id']);
        $this->assertArrayNotHasKey('air_files_folder_id', $stored);
        $this->assertArrayNotHasKey('inventory_files_folder_id', $stored);

        $cleared = $service->updateModuleSettings((string) $module->id, [
            'signed_documents_root_folder_id' => '',
        ]);

        $this->assertArrayNotHasKey('signed_documents_root_folder_id', $cleared);

        $context = $service->contexts()->first();
        $fields = collect($context['fields'])->keyBy('key');

        $this->assertSame('legacy-air-folder', $fields['signed_documents_root_folder_id']['effective_value']);
        $this->assertSame('Config fallback (gso.storage.air_files_folder_id)', $fields['signed_documents_root_folder_id']['source']);
    }

    private function makeService(): GoogleDriveModuleStorageSettingsService
    {
        return new GoogleDriveModuleStorageSettingsService(new GoogleDriveSettingsProvider());
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

    private function seedConfig(): void
    {
        config()->set('services.google_drive.folder_id', 'global-folder');
        config()->set('gso.storage.inspection_photos_folder_id', 'legacy-inspection-folder');
        config()->set('gso.storage.air_unit_files_folder_id', 'legacy-unit-folder');
        config()->set('gso.storage.air_files_folder_id', 'legacy-air-folder');
        config()->set('gso.storage.inventory_files_folder_id', 'legacy-inventory-folder');
        config()->set('google-drive-storage.modules', [
            'GSO' => [
                'setting_key' => 'storage.google_drive',
                'title' => 'GSO Google Drive Storage Plan',
                'description' => 'Folder roots used by GSO.',
                'notes' => [
                    'Signed documents should use document numbers.',
                ],
                'fields' => [
                    'signed_documents_root_folder_id' => [
                        'label' => 'Signed Documents Root Folder ID',
                        'stored_keys' => ['signed_documents_root_folder_id', 'air_files_folder_id'],
                        'fallback_config_keys' => ['gso.storage.air_files_folder_id'],
                    ],
                    'air_inspections_root_folder_id' => [
                        'label' => 'AIR Inspections Root Folder ID',
                        'stored_keys' => ['air_inspections_root_folder_id', 'inspection_photos_folder_id', 'air_unit_files_folder_id'],
                        'fallback_config_keys' => ['gso.storage.inspection_photos_folder_id', 'gso.storage.air_unit_files_folder_id'],
                    ],
                    'inventory_items_root_folder_id' => [
                        'label' => 'Inventory Items Root Folder ID',
                        'stored_keys' => ['inventory_items_root_folder_id', 'inventory_files_folder_id'],
                        'fallback_config_keys' => ['gso.storage.inventory_files_folder_id'],
                    ],
                ],
            ],
        ]);
    }
}
