<?php

namespace App\Core\Services\Access;

use App\Core\Models\Module;
use App\Core\Models\User;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class ModuleAccessService implements ModuleAccessServiceInterface
{
    public function __construct(
        private readonly ModuleDepartmentResolverInterface $moduleDepartments,
    ) {}

    public function hasActiveModuleAccess(User $user, string $moduleId): bool
    {
        $module = Module::query()
            ->select(['id', 'code', 'type', 'name', 'default_department_id', 'is_active'])
            ->whereKey($moduleId)
            ->first();

        if ($module?->isPlatformContext()) {
            return $this->hasPlatformContextAccess($user);
        }

        return $user->userModules()
            ->where('module_id', $moduleId)
            ->where('is_active', true)
            ->exists();
    }

    public function hasAnyActiveModuleAccess(User $user): bool
    {
        return $this->accessibleModulesForUser($user)->isNotEmpty();
    }

    public function grantActiveModuleAccess(User $user, string $moduleId, ?string $departmentId): void
    {
        $resolvedDepartmentId = $departmentId ?: $this->moduleDepartments->resolveForModule($moduleId);

        $user->userModules()->updateOrCreate(
            ['module_id' => $moduleId],
            [
                'department_id' => $resolvedDepartmentId ?: null,
                'is_active' => true,
                'granted_at' => Carbon::now(),
                'revoked_at' => null,
            ]
        );
    }

    public function accessibleModulesForUser(User $user): Collection
    {
        $modules = Module::query()
            ->select('modules.*')
            ->join('user_modules', function ($join) use ($user) {
                $join->on('user_modules.module_id', '=', 'modules.id')
                    ->where('user_modules.user_id', '=', $user->id)
                    ->where('user_modules.is_active', '=', true);
            })
            ->with('defaultDepartment')
            ->where('modules.is_active', true)
            ->orderBy('modules.name')
            ->distinct()
            ->get();

        $platformModule = $this->findPlatformModule();

        if ($platformModule && $this->hasPlatformContextAccess($user)) {
            $alreadyIncluded = $modules->contains(
                fn (Module $module): bool => (string) $module->id === (string) $platformModule->id
            );

            if (! $alreadyIncluded) {
                $modules->prepend($platformModule);
            }
        }

        return $modules
            ->sortBy(fn (Module $module): string => sprintf(
                '%d:%s',
                $module->isPlatformContext() ? 0 : 1,
                mb_strtolower((string) $module->name)
            ))
            ->values();
    }

    public function switchableModulesForUser(User $user): Collection
    {
        $sharedCapabilityCodes = collect((array) config('modules.shared_capability_codes', []))
            ->map(fn (mixed $code): string => Str::upper(trim((string) $code)))
            ->filter()
            ->values()
            ->all();

        return $this->accessibleModulesForUser($user)
            ->reject(function (Module $module) use ($sharedCapabilityCodes): bool {
                return in_array(Str::upper((string) $module->code), $sharedCapabilityCodes, true);
            })
            ->values();
    }

    public function findActiveModuleByCode(string $moduleCode): ?Module
    {
        $moduleCode = Str::upper(trim($moduleCode));

        if ($moduleCode === '') {
            return null;
        }

        return Module::query()
            ->with('defaultDepartment')
            ->where('code', $moduleCode)
            ->where('is_active', true)
            ->first();
    }

    public function rememberActiveModule(Module|string $module): void
    {
        $resolvedModule = $this->resolveModule($module);

        if (! app()->bound('request')) {
            return;
        }

        session([
            'current_module_id' => (string) $resolvedModule->id,
            'current_module_code' => (string) $resolvedModule->code,
        ]);
    }

    public function forgetActiveModule(): void
    {
        if (! app()->bound('request')) {
            return;
        }

        session()->forget([
            'current_module_id',
            'current_module_code',
        ]);
    }

    public function homeRouteNameForModule(Module|string $module): string
    {
        $resolvedModule = $this->resolveModule($module);
        $moduleCode = Str::upper((string) $resolvedModule->code);

        $configuredRoute = (string) data_get(
            config('modules.registry', []),
            $moduleCode . '.home_route',
            ''
        );

        if ($configuredRoute !== '') {
            return $configuredRoute;
        }

        $sharedCapabilityRoute = (string) data_get(
            config('modules.shared_capability_home_routes', []),
            $moduleCode,
            ''
        );

        if ($sharedCapabilityRoute !== '') {
            return $sharedCapabilityRoute;
        }

        return 'modules.index';
    }

    public function homePathForModule(Module|string $module): string
    {
        return route($this->homeRouteNameForModule($module));
    }

    public function postLoginRedirectPathForUser(User $user): string
    {
        $modules = $this->switchableModulesForUser($user);

        if ($modules->isEmpty()) {
            $modules = $this->accessibleModulesForUser($user);
        }

        if ($modules->isEmpty()) {
            throw new RuntimeException('The user does not have active access to any platform module.');
        }

        if ($modules->count() === 1) {
            $module = $modules->first();
            $this->rememberActiveModule($module);

            return $this->homePathForModule($module);
        }

        return route('modules.index');
    }

    protected function resolveModule(Module|string $module): Module
    {
        if ($module instanceof Module) {
            return $module;
        }

        $resolvedModule = $this->findActiveModuleByCode($module);

        if (! $resolvedModule) {
            throw new RuntimeException("Active module [{$module}] was not found.");
        }

        return $resolvedModule;
    }

    protected function findPlatformModule(): ?Module
    {
        return Module::query()
            ->with('defaultDepartment')
            ->where('code', 'CORE')
            ->where('is_active', true)
            ->first();
    }

    protected function hasPlatformContextAccess(User $user): bool
    {
        return in_array(mb_strtolower(trim((string) $user->user_type)), ['admin', 'administrator'], true);
    }
}
