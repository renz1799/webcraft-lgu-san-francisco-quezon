<?php

namespace App\Providers;

use App\Services\ThemeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        // Make ThemeService a singleton (cached, cheap to resolve).
        $this->app->singleton(ThemeService::class, function ($app) {
            return new ThemeService($app->make(CacheRepository::class));
        });
    }

    /**
     * Bootstrap application services.
     */
    public function boot(ThemeService $theme): void
    {
        // Share theme data with every Blade view
        View::composer('*', function ($view) use ($theme) {
            $user        = Auth::user();
            $themeStyle  = $user
                ? $theme->getUserStyle($user->id)
                : ThemeService::defaults()['style'];

            $themeColors = $theme->getGlobalColors();

            $view->with('themeStyle', $themeStyle)
                 ->with('themeColors', $themeColors);
        });
    }
}
