<?php

namespace App\Services\Access;

use App\Models\User;
use App\Services\Contracts\ModuleAccessServiceInterface;

class ModuleAccessService implements ModuleAccessServiceInterface
{
    public function hasActiveModuleAccess(User $user, string $moduleId): bool
    {
        return $user->userModules()
            ->where('module_id', $moduleId)
            ->where('is_active', true)
            ->exists();
    }
}