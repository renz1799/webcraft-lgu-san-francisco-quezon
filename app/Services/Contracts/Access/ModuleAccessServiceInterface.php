<?php

namespace App\Services\Contracts\Access;

use App\Models\User;

interface ModuleAccessServiceInterface
{
    public function hasActiveModuleAccess(User $user, string $moduleId): bool;
}