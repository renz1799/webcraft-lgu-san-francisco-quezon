<?php

namespace App\Core\Services\Access;

use App\Core\Models\Department;
use App\Core\Models\Module;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Support\Collection;
use RuntimeException;

class ModuleDepartmentResolver implements ModuleDepartmentResolverInterface
{
    public function __construct(
        private readonly CurrentContext $context,
    ) {}

    public function resolveForModule(?string $moduleId = null, ?string $explicitDepartmentId = null): ?string
    {
        $explicitDepartmentId = trim((string) $explicitDepartmentId);

        if ($explicitDepartmentId !== '') {
            if ($this->departmentBelongsToModule($explicitDepartmentId, $moduleId)) {
                return $explicitDepartmentId;
            }

            throw new RuntimeException("Department [{$explicitDepartmentId}] does not belong to the selected module.");
        }

        return $this->defaultDepartmentIdForModule($moduleId);
    }

    public function defaultDepartmentIdForModule(?string $moduleId = null): ?string
    {
        $module = $this->resolveModule($moduleId);

        if ($module) {
            if ($module->default_department_id !== null) {
                return $module->default_department_id;
            }

            $configuredDepartmentId = $this->resolveConfiguredDepartmentIds($module->code)->first();

            if ($configuredDepartmentId !== null) {
                return $configuredDepartmentId;
            }
        }

        return $this->context->defaultDepartmentId();
    }

    public function allowedDepartmentsForModule(?string $moduleId = null): Collection
    {
        $module = $this->resolveModule($moduleId);
        $allowedIds = collect();

        if ($module) {
            if ($module->default_department_id) {
                $allowedIds->push((string) $module->default_department_id);
            }

            $allowedIds = $allowedIds->merge($this->resolveConfiguredDepartmentIds($module->code));
        }

        if ($allowedIds->isEmpty()) {
            $fallback = $this->defaultDepartmentIdForModule($moduleId);

            if ($fallback) {
                $allowedIds->push((string) $fallback);
            }
        }

        $orderedIds = $allowedIds
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();

        if ($orderedIds->isEmpty()) {
            return collect();
        }

        $departments = Department::query()
            ->whereIn('id', $orderedIds->all())
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get(['id', 'name', 'code'])
            ->keyBy(fn ($department) => (string) $department->id);

        return $orderedIds
            ->map(fn ($id) => $departments->get((string) $id))
            ->filter()
            ->values();
    }

    public function departmentBelongsToModule(string $departmentId, ?string $moduleId = null): bool
    {
        $departmentId = trim((string) $departmentId);

        if ($departmentId === '') {
            return false;
        }

        return $this->allowedDepartmentsForModule($moduleId)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->contains($departmentId);
    }

    private function resolveModule(?string $moduleId): ?Module
    {
        $moduleId = trim((string) $moduleId);

        if ($moduleId === '') {
            return $this->context->module();
        }

        $currentModule = $this->context->module();

        if ($currentModule && (string) $currentModule->id === $moduleId) {
            return $currentModule;
        }

        return Module::query()->find($moduleId);
    }

    private function resolveConfiguredDepartmentIds(?string $moduleCode): Collection
    {
        $moduleCode = strtoupper(trim((string) $moduleCode));

        if ($moduleCode === '') {
            return collect();
        }

        $configuredCodes = collect(data_get(
            config('modules.department_scopes', []),
            $moduleCode . '.codes',
            []
        ))
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter();

        if ($configuredCodes->isEmpty()) {
            $defaultCode = strtoupper(trim((string) data_get(
                config('modules.department_defaults', []),
                $moduleCode . '.code',
                ''
            )));

            if ($defaultCode !== '') {
                $configuredCodes->push($defaultCode);
            }
        }

        if ($configuredCodes->isEmpty()) {
            return collect();
        }

        return Department::query()
            ->whereIn('code', $configuredCodes->all())
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get(['id', 'code'])
            ->sortBy(function ($department) use ($configuredCodes) {
                return $configuredCodes->search(strtoupper((string) $department->code));
            })
            ->pluck('id')
            ->values();
    }
}
