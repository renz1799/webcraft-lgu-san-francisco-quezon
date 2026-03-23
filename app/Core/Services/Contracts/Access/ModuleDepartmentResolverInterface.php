<?php

namespace App\Core\Services\Contracts\Access;

use Illuminate\Support\Collection;

interface ModuleDepartmentResolverInterface
{
    public function resolveForModule(?string $moduleId = null, ?string $explicitDepartmentId = null): ?string;

    public function defaultDepartmentIdForModule(?string $moduleId = null): ?string;

    public function allowedDepartmentsForModule(?string $moduleId = null): Collection;

    public function departmentBelongsToModule(string $departmentId, ?string $moduleId = null): bool;
}
