<?php

namespace App\Core\Services\Contracts\Access;

use App\Core\Models\Module;
use App\Core\Models\User;
use Illuminate\Support\Collection;

interface ModuleAccessServiceInterface
{
    public function hasActiveModuleAccess(User $user, string $moduleId): bool;

    public function hasAnyActiveModuleAccess(User $user): bool;

    public function grantActiveModuleAccess(User $user, string $moduleId, ?string $departmentId): void;

    public function accessibleModulesForUser(User $user): Collection;

    public function switchableModulesForUser(User $user): Collection;

    public function findActiveModuleByCode(string $moduleCode): ?Module;

    public function rememberActiveModule(Module|string $module): void;

    public function forgetActiveModule(): void;

    public function homeRouteNameForModule(Module|string $module): string;

    public function homePathForModule(Module|string $module): string;

    public function postLoginRedirectPathForUser(User $user): string;
}
