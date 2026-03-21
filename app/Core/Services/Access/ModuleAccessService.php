<?php

namespace App\Core\Services\Access;

use App\Core\Models\User;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use Illuminate\Support\Carbon;
use App\Core\Support\CurrentContext;

class ModuleAccessService implements ModuleAccessServiceInterface
{
    public function hasActiveModuleAccess(User $user, string $moduleId): bool
    {
        return $user->userModules()
            ->where('module_id', $moduleId)
            ->where('is_active', true)
            ->exists();
    }

public function grantActiveModuleAccess(User $user, string $moduleId, ?string $departmentId): void
{
    $user->userModules()->updateOrCreate(
        ['module_id' => $moduleId],
        [
            'department_id' => $departmentId ?: null,
            'is_active' => true,
            'granted_at' => Carbon::now(),
            'revoked_at' => null,
        ]
    );
}
}