<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Repositories\Eloquent\EloquentThemePreferencesRepository;
use App\Services\UI\ThemeService;
use App\Support\CurrentContext;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ThemeServiceTest extends TestCase
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

    public function test_user_style_is_saved_per_user_and_merged_with_defaults(): void
    {
        $service = $this->makeService();

        $saved = $service->saveUserStyle('user-1', [
            'mode' => 'dark',
            'nav' => 'horizontal',
            'menuStyle' => 'menu-hover',
        ]);

        $this->assertSame('dark', $saved['mode']);
        $this->assertSame('horizontal', $saved['nav']);
        $this->assertSame('menu-hover', $saved['menuStyle']);
        $this->assertSame('ltr', $saved['dir']);
        $this->assertSame('fixed', $saved['menuPosition']);

        $otherUser = $service->getUserStyle('user-2');

        $this->assertSame(ThemeService::defaults()['style']['mode'], $otherUser['mode']);
        $this->assertSame(ThemeService::defaults()['style']['nav'], $otherUser['nav']);
    }

    public function test_module_colors_are_scoped_per_module(): void
    {
        $moduleA = Module::query()->create([
            'code' => 'MODA',
            'name' => 'Module A',
            'description' => 'Alpha module',
            'url' => 'https://module-a.test',
            'is_active' => true,
        ]);

        $moduleB = Module::query()->create([
            'code' => 'MODB',
            'name' => 'Module B',
            'description' => 'Beta module',
            'url' => 'https://module-b.test',
            'is_active' => true,
        ]);

        $this->makeService((string) $moduleA->id)->saveModuleColors([
            'menu' => 'gradient',
            'header' => 'transparent',
            'primaryRgb' => '92, 144, 163',
            'primaryRgb1' => '92 144 163',
            'bgImage' => 'bgimg2',
        ]);

        $this->makeService((string) $moduleB->id)->saveModuleColors([
            'menu' => 'dark',
            'primaryRgb' => '223, 90, 90',
            'primaryRgb1' => '223 90 90',
        ]);

        $colorsA = $this->makeService((string) $moduleA->id)->getModuleColors();
        $colorsB = $this->makeService((string) $moduleB->id)->getModuleColors();

        $this->assertSame('gradient', $colorsA['menu']);
        $this->assertSame('transparent', $colorsA['header']);
        $this->assertSame('92, 144, 163', $colorsA['primaryRgb']);
        $this->assertSame('bgimg2', $colorsA['bgImage']);

        $this->assertSame('dark', $colorsB['menu']);
        $this->assertSame('223, 90, 90', $colorsB['primaryRgb']);
        $this->assertArrayNotHasKey('bgImage', $colorsB);
    }

    public function test_save_module_colors_requires_active_module_when_missing_context(): void
    {
        $service = $this->makeService();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Active module context is required to save theme colors.');

        $service->saveModuleColors([
            'menu' => 'dark',
        ]);
    }

    private function makeService(?string $moduleId = null): ThemeService
    {
        config()->set('module.id', $moduleId);

        return new ThemeService(
            $this->app->make(CacheRepository::class),
            new EloquentThemePreferencesRepository(),
            new CurrentContext(),
        );
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

        Schema::create('user_preferences', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->text('theme_style')->nullable();
            $table->timestamps();
        });
    }
}
