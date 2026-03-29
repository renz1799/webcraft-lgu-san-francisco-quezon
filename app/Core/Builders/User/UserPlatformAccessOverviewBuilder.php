<?php

namespace App\Core\Builders\User;

use App\Core\Builders\Contracts\User\UserPlatformAccessOverviewBuilderInterface;
use App\Core\Models\Department;
use App\Core\Models\Module;
use App\Core\Models\User;
use App\Core\Models\UserModule;
use Illuminate\Support\Collection;

class UserPlatformAccessOverviewBuilder implements UserPlatformAccessOverviewBuilderInterface
{
    public function build(User $user): array
    {
        $memberships = $user->relationLoaded('userModules')
            ? $user->userModules
            : collect();

        $roleAssignments = $user->relationLoaded('moduleRoleAssignments')
            ? $user->moduleRoleAssignments
            : collect();

        $membershipsByModule = $memberships
            ->filter(fn (UserModule $membership): bool => filled($membership->module_id))
            ->keyBy(fn (UserModule $membership): string => (string) $membership->module_id);

        $rolesByModule = $roleAssignments
            ->filter(fn ($assignment): bool => filled($assignment->module_id))
            ->groupBy(fn ($assignment): string => (string) $assignment->module_id);

        $moduleIds = $membershipsByModule->keys()
            ->merge($rolesByModule->keys())
            ->filter()
            ->unique()
            ->values();

        $modules = $moduleIds
            ->map(function (string $moduleId) use ($membershipsByModule, $rolesByModule): array {
                $membership = $membershipsByModule->get($moduleId);
                $assignments = $rolesByModule->get($moduleId, collect());
                $module = $membership?->module ?? $assignments->first()?->module;
                $roles = $assignments
                    ->map(fn ($assignment): ?string => $assignment->role?->name)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                [$statusKey, $statusLabel] = $this->resolveModuleAccessStatus($membership, $roles);

                return [
                    'module_id' => $moduleId,
                    'module_code' => trim((string) ($module?->code ?? '')),
                    'module_name' => trim((string) ($module?->name ?? 'Unknown Module')),
                    'module_label' => $this->moduleLabel($module),
                    'module_type' => $module?->typeLabel() ?? 'Module',
                    'access_status_key' => $statusKey,
                    'access_status_label' => $statusLabel,
                    'is_active_access' => (bool) ($membership?->is_active),
                    'department_label' => $this->departmentLabel($membership?->department),
                    'roles' => $roles,
                    'granted_at_text' => $membership?->granted_at?->format('M d, Y h:i A') ?? '-',
                    'revoked_at_text' => $membership?->revoked_at?->format('M d, Y h:i A') ?? '-',
                ];
            })
            ->sort(function (array $left, array $right): int {
                if ($left['is_active_access'] !== $right['is_active_access']) {
                    return $left['is_active_access'] ? -1 : 1;
                }

                return strcasecmp($left['module_label'], $right['module_label']);
            })
            ->values()
            ->all();

        $activeModuleCount = $memberships->where('is_active', true)->count();
        $inactiveModuleCount = max(0, count($modules) - $activeModuleCount);

        return [
            'user' => [
                'id' => (string) $user->getKey(),
                'display_name' => $this->displayName($user),
                'username' => (string) ($user->username ?? '-'),
                'email' => (string) ($user->email ?? '-'),
                'platform_status_key' => $this->platformStatusKey($user),
                'platform_status_label' => $this->platformStatusLabel($user),
                'home_department_label' => $this->departmentLabel($user->primaryDepartment),
                'last_login_at_text' => $user->last_login_at?->format('M d, Y h:i A') ?? 'Never',
                'created_at_text' => $user->created_at?->format('M d, Y h:i A') ?? '-',
            ],
            'summary' => [
                'active_module_count' => $activeModuleCount,
                'inactive_module_count' => $inactiveModuleCount,
                'has_active_module_access' => $activeModuleCount > 0,
                'is_multi_module' => $activeModuleCount > 1,
            ],
            'modules' => $modules,
        ];
    }

    private function displayName(User $user): string
    {
        $profileName = trim((string) ($user->profile?->full_name ?? ''));

        if ($profileName !== '') {
            return $profileName;
        }

        return trim((string) ($user->username ?? $user->email ?? 'User'));
    }

    private function platformStatusKey(User $user): string
    {
        if ($user->deleted_at !== null) {
            return 'archived';
        }

        return $user->is_active ? 'active' : 'inactive';
    }

    private function platformStatusLabel(User $user): string
    {
        return match ($this->platformStatusKey($user)) {
            'archived' => 'Archived',
            'inactive' => 'Inactive',
            default => 'Active',
        };
    }

    private function departmentLabel(?Department $department): string
    {
        if (! $department) {
            return 'Unassigned';
        }

        $code = trim((string) ($department->code ?? ''));
        $name = trim((string) ($department->name ?? ''));

        if ($code !== '' && $name !== '') {
            return $code . ' - ' . $name;
        }

        return $name !== '' ? $name : 'Unassigned';
    }

    private function moduleLabel(?Module $module): string
    {
        if (! $module) {
            return 'Unknown Module';
        }

        $code = trim((string) ($module->code ?? ''));
        $name = trim((string) ($module->name ?? ''));

        if ($code !== '' && $name !== '') {
            return strtoupper($code) . ' - ' . $name;
        }

        return $name !== '' ? $name : strtoupper($code);
    }

    private function resolveModuleAccessStatus(?UserModule $membership, array $roles): array
    {
        if ($membership?->is_active) {
            return ['active', 'Active access'];
        }

        if ($membership && $membership->revoked_at !== null) {
            return ['revoked', 'Revoked'];
        }

        if ($membership) {
            return ['inactive', 'Inactive'];
        }

        if ($roles !== []) {
            return ['roles_only', 'Roles only'];
        }

        return ['unassigned', 'Unassigned'];
    }
}
