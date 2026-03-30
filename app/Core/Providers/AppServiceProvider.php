<?php

namespace App\Core\Providers;

use App\Core\Repositories\Contracts\ThemePreferencesRepositoryInterface;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Services\UI\ThemeService;
use App\Core\Support\AdminRouteResolver;
use App\Core\Support\CurrentContext;
use App\Core\Support\ProfileRouteResolver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $printables = config('printables', []);
        $legacyModules = config('print.modules', []);

        if (is_array($printables)) {
            config()->set('print.modules', array_replace_recursive(
                is_array($legacyModules) ? $legacyModules : [],
                $printables,
            ));
        }

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

        $this->app->singleton(AdminRouteResolver::class, function ($app) {
            return new AdminRouteResolver(
                $app->make(CurrentContext::class),
            );
        });

        $this->app->singleton(ProfileRouteResolver::class, function ($app) {
            return new ProfileRouteResolver(
                $app->make(CurrentContext::class),
                $app->make(ModuleAccessServiceInterface::class),
            );
        });
    }

    public function boot(ThemeService $theme): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->input('email', '')));
            $key = $email !== '' ? ($email . '|' . $request->ip()) : $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('password-reset-link', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->input('email', '')));
            $key = $email !== ''
                ? ('password-reset-link|' . $email . '|' . $request->ip())
                : ('password-reset-link|' . $request->ip());

            return Limit::perMinute(3)->by($key);
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = mb_strtolower(trim((string) $request->input('email', '')));
            $key = $email !== ''
                ? ('password-reset|' . $email . '|' . $request->ip())
                : ('password-reset|' . $request->ip());

            return Limit::perMinute(5)->by($key);
        });

        View::composer(['layouts.master', 'layouts.custom-master', 'profile.index'], function ($view) use ($theme) {
            $user = Auth::user();
            $currentModule = app(CurrentContext::class)->module();
            $accessibleModules = collect();

            if ($user && $this->platformModuleTablesAvailable()) {
                $accessibleModules = app(ModuleAccessServiceInterface::class)
                    ->switchableModulesForUser($user);
            }

            $themeStyle = $user
                ? $theme->getUserStyle((string) $user->id)
                : ThemeService::defaults()['style'];

            $themeColors = $theme->getModuleColors();
            $profileRoutes = $this->app->make(ProfileRouteResolver::class)->routesFor($user);

            $view->with('themeStyle', $themeStyle)
                ->with('themeColors', $themeColors)
                ->with('currentModule', $currentModule)
                ->with('accessibleModules', $accessibleModules)
                ->with('profileRoutes', $profileRoutes);
        });

        View::share('adminRoutes', $this->app->make(AdminRouteResolver::class));
    }

    private function platformModuleTablesAvailable(): bool
    {
        try {
            return Schema::hasTable('modules') && Schema::hasTable('user_modules');
        } catch (Throwable) {
            return false;
        }
    }
}
