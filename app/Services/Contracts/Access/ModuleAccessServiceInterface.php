<?php

namespace App\Services\Contracts\Access;

use App\Models\User;

interface ModuleAccessServiceInterface
{
    public function hasActiveModuleAccess(User $user, string $moduleId): bool;

    public function grantActiveModuleAccess(User $user, string $moduleId, ?string $departmentId): void;
}