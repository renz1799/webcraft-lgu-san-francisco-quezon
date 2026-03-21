<?php

namespace App\Core\Services\Access;

use App\Core\Models\User;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use Illuminate\Support\Carbon;

class ModuleAccessService implements ModuleAccessServiceInterface
{
    public function __construct(
        private readonly ModuleDepartmentResolverInterface $moduleDepartments,
    ) {}

    public function hasActiveModuleAccess(User $user, string $moduleId): bool
    {
        return $user->userModules()
            ->where('module_id', $moduleId)
            ->where('is_active', true)
            ->exists();
    }

    public function grantActiveModuleAccess(User $user, string $moduleId, ?string $departmentId): void
    {
        $resolvedDepartmentId = $departmentId ?: $this->moduleDepartments->resolveForModule($moduleId);

        $user->userModules()->updateOrCreate(
            ['module_id' => $moduleId],
            [
                'department_id' => $resolvedDepartmentId ?: null,
                'is_active' => true,
                'granted_at' => Carbon::now(),
                'revoked_at' => null,
            ]
        );
    }
}
