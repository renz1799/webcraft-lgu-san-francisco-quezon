<?php

namespace App\Core\Services\Contracts\Access;

use App\Core\Models\User;

interface ModuleAccessServiceInterface
{
    public function hasActiveModuleAccess(User $user, string $moduleId): bool;

    public function grantActiveModuleAccess(User $user, string $moduleId, ?string $departmentId): void;
}