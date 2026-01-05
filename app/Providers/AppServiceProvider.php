<?php

namespace App\Providers;

use App\Services\ThemeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use App\Repositories\Contracts\ThemePreferencesRepositoryInterface;

// ✅ add these imports
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

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
        /**
         * ✅ Named rate limiter for login attempts
         * Keyed by email + IP (better than IP-only, avoids office-wide lockout)
         */
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email', '');
            $email = mb_strtolower(trim($email));

            // If email is missing for some reason, fall back to IP-only
            $key = $email !== '' ? ($email . '|' . $request->ip()) : $request->ip();

            return Limit::perMinute(5)->by($key);
        });

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
