<?php

namespace App\Core\Support;

use App\Core\Models\Department;
use App\Core\Models\Module;

class CurrentContext
{
    protected ?Module $module = null;
    protected ?Department $defaultDepartment = null;

    public function module(): ?Module
    {
        if ($this->module !== null) {
            return $this->module;
        }

        $moduleId = config('module.id');

        if (! $moduleId) {
            return null;
        }

        return $this->module = Module::query()->find($moduleId);
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
        if ($this->defaultDepartment !== null) {
            return $this->defaultDepartment;
        }

        $departmentId = config('department.id');

        if (! $departmentId) {
            return null;
        }

        return $this->defaultDepartment = Department::query()->find($departmentId);
    }

    public function defaultDepartmentId(): ?string
    {
        return $this->defaultDepartment()?->id;
    }

    public function defaultDepartmentCode(): ?string
    {
        return $this->defaultDepartment()?->code ?? config('department.code');
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
}
