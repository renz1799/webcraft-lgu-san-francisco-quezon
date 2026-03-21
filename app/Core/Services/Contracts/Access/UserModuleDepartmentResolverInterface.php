<?php

namespace App\Core\Services\Contracts\Access;

interface UserModuleDepartmentResolverInterface
{
    public function resolveForUser(?string $userId, ?string $moduleId = null): ?string;
}
