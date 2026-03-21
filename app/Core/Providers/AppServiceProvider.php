<?php

namespace App\Core\Providers;

use App\Core\Repositories\Contracts\ThemePreferencesRepositoryInterface;
use App\Core\Services\UI\ThemeService;
use App\Core\Support\CurrentContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeService::class, function ($app) {
            return new ThemeService(
                $app->make(CacheRepository::class),
                $app->make(ThemePreferencesRepositoryInterface::class),
                $app->make(CurrentContext::class),
            );
        });

        $this->app->singleton(CurrentContext::class, function () {
            return new CurrentContext();
        });
    }

    public function boot(ThemeService $theme): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->input('email', '')));
            $key = $email !== '' ? ($email . '|' . $request->ip()) : $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        View::composer(['layouts.master', 'layouts.custom-master'], function ($view) use ($theme) {
            $user = Auth::user();

            $themeStyle = $user
                ? $theme->getUserStyle((string) $user->id)
                : ThemeService::defaults()['style'];

            $themeColors = $theme->getModuleColors();

            $view->with('themeStyle', $themeStyle)
                ->with('themeColors', $themeColors);
        });
    }
}
