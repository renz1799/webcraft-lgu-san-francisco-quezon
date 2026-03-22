<?php

namespace App\Core\Support;

use App\Core\Models\Department;
use App\Core\Models\Module;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class CurrentContext
{
    protected ?Module $module = null;
    protected ?Department $defaultDepartment = null;
    protected bool $moduleResolved = false;
    protected bool $defaultDepartmentResolved = false;

    public function setModule(Module|string|null $module): void
    {
        if (is_string($module)) {
            $module = $this->resolveActiveModuleByCode($module);
        }

        $this->module = $module;
        $this->moduleResolved = true;
        $this->defaultDepartment = $module?->relationLoaded('defaultDepartment')
            ? $module->defaultDepartment
            : null;
        $this->defaultDepartmentResolved = $module?->relationLoaded('defaultDepartment') ?? false;
    }

    public function clearModule(): void
    {
        $this->module = null;
        $this->defaultDepartment = null;
        $this->moduleResolved = true;
        $this->defaultDepartmentResolved = true;
    }

    public function module(): ?Module
    {
        if ($this->moduleResolved) {
            return $this->module;
        }

        if (! $this->platformTableAvailable('modules')) {
            $this->moduleResolved = true;

            return null;
        }

        if ($this->usesPlatformContext()) {
            $platformModule = $this->resolvePlatformModule();

            if ($platformModule) {
                $this->module = $platformModule;
                $this->moduleResolved = true;

                return $this->module;
            }
        }

        if ($this->runningInHttpContext()) {
            $sessionModuleId = session('current_module_id');

            if (is_string($sessionModuleId) && $sessionModuleId !== '') {
                $module = Module::query()
                    ->with('defaultDepartment')
                    ->whereKey($sessionModuleId)
                    ->where('is_active', true)
                    ->first();

                if ($module) {
                    $this->module = $module;
                    $this->moduleResolved = true;

                    return $this->module;
                }
            }
        }

        $moduleId = config('module.id');

        if (! $moduleId) {
            $this->moduleResolved = true;

            return null;
        }

        $this->module = Module::query()
            ->with('defaultDepartment')
            ->whereKey($moduleId)
            ->first();
        $this->moduleResolved = true;

        return $this->module;
    }

    public function moduleId(): ?string
    {
        return $this->module()?->id;
    }

    public function moduleCode(): ?string
    {
        return $this->module()?->code ?? config('module.code');
    }

    public function defaultDepartment(): ?Department
    {
        if ($this->defaultDepartmentResolved) {
            return $this->defaultDepartment;
        }

        $module = $this->module();
        if ($module?->defaultDepartment) {
            $this->defaultDepartment = $module->defaultDepartment;
            $this->defaultDepartmentResolved = true;

            return $this->defaultDepartment;
        }

        $departmentId = config('department.id');

        if (! $departmentId || ! $this->platformTableAvailable('departments')) {
            $this->defaultDepartmentResolved = true;

            return null;
        }

        $this->defaultDepartment = Department::query()->find($departmentId);
        $this->defaultDepartmentResolved = true;

        return $this->defaultDepartment;
    }

    public function defaultDepartmentId(): ?string
    {
        return $this->defaultDepartment()?->id;
    }

    public function defaultDepartmentCode(): ?string
    {
        return $this->defaultDepartment()?->code ?? config('department.code');
    }

    public function usesPlatformContext(): bool
    {
        if (! $this->runningInHttpContext()) {
            return false;
        }

        $routeName = request()->route()?->getName();

        if (! is_string($routeName) || $routeName === '') {
            return false;
        }

        foreach ((array) config('modules.platform_context_route_names', []) as $pattern) {
            if (is_string($pattern) && $pattern !== '' && Str::is($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    public function toArray(): array
    {
        return [
            'module_id' => $this->moduleId(),
            'module_code' => $this->moduleCode(),
            'default_department_id' => $this->defaultDepartmentId(),
            'default_department_code' => $this->defaultDepartmentCode(),
        ];
    }

    protected function resolveActiveModuleByCode(string $moduleCode): ?Module
    {
        $moduleCode = Str::upper(trim($moduleCode));

        if ($moduleCode === '' || ! $this->platformTableAvailable('modules')) {
            return null;
        }

        return Module::query()
            ->with('defaultDepartment')
            ->where('code', $moduleCode)
            ->where('is_active', true)
            ->first();
    }

    protected function resolvePlatformModule(): ?Module
    {
        return Module::query()
            ->with('defaultDepartment')
            ->where('code', 'CORE')
            ->where('is_active', true)
            ->first();
    }

    protected function runningInHttpContext(): bool
    {
        return app()->bound('request');
    }

    protected function platformTableAvailable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }
}
