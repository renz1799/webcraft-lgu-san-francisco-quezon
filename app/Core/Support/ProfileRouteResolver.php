<?php

namespace App\Core\Support;

use App\Core\Models\Module;
use App\Core\Models\User;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use Illuminate\Support\Facades\Route;

class ProfileRouteResolver
{
    public function __construct(
        private readonly CurrentContext $context,
        private readonly ModuleAccessServiceInterface $moduleAccess,
    ) {}

    public function routesFor(?User $user = null): array
    {
        $resolvedUser = $this->resolveUser($user);
        $indexRouteName = $this->indexRouteName($resolvedUser);

        return [
            'index' => route($indexRouteName),
            'personal_info' => route($indexRouteName, ['tab' => 'personal-info']),
            'account_settings' => route($indexRouteName, ['tab' => 'account-settings']),
            'update' => route($this->updateRouteName($resolvedUser)),
            'update_password' => route($this->updatePasswordRouteName($resolvedUser)),
            'route_names' => [
                'index' => $indexRouteName,
                'update' => $this->updateRouteName($resolvedUser),
                'update_password' => $this->updatePasswordRouteName($resolvedUser),
            ],
            'module_code' => $this->preferredModule($resolvedUser)?->code,
            'is_module_contextual' => $indexRouteName !== 'profile.index',
        ];
    }

    public function indexRouteName(?User $user = null): string
    {
        return $this->moduleRouteNameForAction('index', $user) ?? 'profile.index';
    }

    public function updateRouteName(?User $user = null): string
    {
        return $this->moduleRouteNameForAction('update', $user) ?? 'profile.update';
    }

    public function updatePasswordRouteName(?User $user = null): string
    {
        return $this->moduleRouteNameForAction('updatePassword', $user) ?? 'profile.updatePassword';
    }

    public function indexUrl(?User $user = null, array $parameters = []): string
    {
        return route($this->indexRouteName($user), $parameters);
    }

    public function accountSettingsUrl(?User $user = null): string
    {
        return $this->indexUrl($user, ['tab' => 'account-settings']);
    }

    public function personalInfoUrl(?User $user = null): string
    {
        return $this->indexUrl($user, ['tab' => 'personal-info']);
    }

    public function shouldRedirectGenericProfile(?User $user = null): bool
    {
        return $this->indexRouteName($user) !== 'profile.index';
    }

    public function preferredModule(?User $user = null): ?Module
    {
        $resolvedUser = $this->resolveUser($user);

        if (! $resolvedUser) {
            return null;
        }

        $currentContextModule = $this->currentContextModule($resolvedUser);

        if ($currentContextModule) {
            return $currentContextModule;
        }

        $rememberedModule = $this->rememberedModule($resolvedUser);

        if ($rememberedModule) {
            return $rememberedModule;
        }

        return $this->singleAccessibleModule($resolvedUser);
    }

    private function moduleRouteNameForAction(string $action, ?User $user = null): ?string
    {
        $module = $this->preferredModule($user);

        if (! $module) {
            return null;
        }

        $routeName = strtolower((string) $module->code) . '.profile.' . $action;

        return Route::has($routeName) ? $routeName : null;
    }

    private function currentContextModule(User $user): ?Module
    {
        $module = $this->context->module();

        if (! $module || $this->context->usesPlatformContext()) {
            return null;
        }

        return $this->isUsableProfileModule($user, $module) ? $module : null;
    }

    private function rememberedModule(User $user): ?Module
    {
        if (! app()->bound('request')) {
            return null;
        }

        $moduleCode = trim((string) session('current_module_code', ''));

        if ($moduleCode === '') {
            return null;
        }

        $module = $this->moduleAccess->findActiveModuleByCode($moduleCode);

        return $module && $this->isUsableProfileModule($user, $module) ? $module : null;
    }

    private function singleAccessibleModule(User $user): ?Module
    {
        $modules = $this->moduleAccess->switchableModulesForUser($user)
            ->filter(fn (Module $module): bool => $this->moduleHasProfileRoutes($module))
            ->values();

        return $modules->count() === 1 ? $modules->first() : null;
    }

    private function isUsableProfileModule(User $user, Module $module): bool
    {
        if ($module->isPlatformContext()) {
            return false;
        }

        if (! $this->moduleHasProfileRoutes($module)) {
            return false;
        }

        return $this->moduleAccess->hasActiveModuleAccess($user, (string) $module->id);
    }

    private function moduleHasProfileRoutes(Module $module): bool
    {
        $routeBase = strtolower((string) $module->code) . '.profile.';

        return Route::has($routeBase . 'index')
            && Route::has($routeBase . 'update')
            && Route::has($routeBase . 'updatePassword');
    }

    private function resolveUser(?User $user): ?User
    {
        return $user ?: auth()->user();
    }
}
