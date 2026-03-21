<?php

namespace App\Core\Services\Contracts\Access;

interface ModuleDepartmentResolverInterface
{
    public function resolveForModule(?string $moduleId = null, ?string $explicitDepartmentId = null): ?string;
}
