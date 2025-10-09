<?php

namespace App\Providers;

use App\Services\ThemeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use App\Repositories\Contracts\ThemePreferencesRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        // ThemeService singleton with cache + repo
        $this->app->singleton(ThemeService::class, function ($app) {
            return new ThemeService(
                $app->make(CacheRepository::class),
                $app->make(ThemePreferencesRepositoryInterface::class) // ← add
            );
        });
    }

    /**
     * Bootstrap application services.
     */
    public function boot(ThemeService $theme): void
    {
        View::composer('*', function ($view) use ($theme) {
            $user = Auth::user();

            $themeStyle  = $user
                ? $theme->getUserStyle((string) $user->id)
                : ThemeService::defaults()['style'];

            $themeColors = $theme->getGlobalColors();

            $view->with('themeStyle', $themeStyle)
                 ->with('themeColors', $themeColors);
        });
    }
}
