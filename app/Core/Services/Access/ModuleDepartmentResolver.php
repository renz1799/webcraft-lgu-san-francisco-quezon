<?php

namespace App\Core\Services\Access;

use App\Core\Models\Department;
use App\Core\Models\Module;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Support\CurrentContext;
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
            return $this->resolveExplicitDepartmentId($explicitDepartmentId);
        }

        $module = $this->resolveModule($moduleId);

        if ($module) {
            $configuredDepartmentId = $this->resolveConfiguredDepartmentId($module->code);

            if ($configuredDepartmentId !== null) {
                return $configuredDepartmentId;
            }
        }

        return $this->context->defaultDepartmentId();
    }

    private function resolveExplicitDepartmentId(string $departmentId): string
    {
        if (Department::query()->whereKey($departmentId)->exists()) {
            return $departmentId;
        }

        throw new RuntimeException("Department [{$departmentId}] was not found.");
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

    private function resolveConfiguredDepartmentId(?string $moduleCode): ?string
    {
        $moduleCode = strtoupper(trim((string) $moduleCode));

        if ($moduleCode === '') {
            return null;
        }

        $departmentCode = trim((string) data_get(
            config('modules.department_defaults', []),
            $moduleCode . '.code',
            ''
        ));

        if ($departmentCode === '') {
            return null;
        }

        return Department::query()
            ->where('code', $departmentCode)
            ->value('id');
    }
}
