<?php

namespace App\Core\Builders\User;

use App\Core\Builders\Contracts\User\UserDatatableRowBuilderInterface;
use App\Core\Models\Department;
use App\Core\Models\Module;
use App\Core\Models\UserModule;
use App\Core\Models\User;
use App\Core\Support\AdminRouteResolver;
use Illuminate\Support\Collection;

class UserDatatableRowBuilder implements UserDatatableRowBuilderInterface
{
    public function __construct(
        private readonly ?AdminRouteResolver $adminRoutes = null,
    ) {}

    public function build(User $user): array
    {
        $adminRoutes = $this->adminRoutes ?? app(AdminRouteResolver::class);

        if (! $adminRoutes->isModuleScoped()) {
            return $this->buildPlatformRow($user);
        }

        $moduleRoleName = $user->getAttribute('current_module_role_name');
        $moduleMembershipStatus = $user->getAttribute('current_module_membership_is_active');

        if (! is_string($moduleRoleName) || trim($moduleRoleName) === '') {
            $moduleRoleName = $user->relationLoaded('moduleRoleAssignments')
                ? ($user->moduleRoleAssignments->first()?->role?->name ?? null)
                : null;
        }

        $resolvedStatus = $moduleMembershipStatus === null
            ? (bool) $user->is_active
            : ((int) $moduleMembershipStatus === 1);

        return [
            'id' => (string) $user->id,
            'username' => (string) ($user->username ?? '-'),
            'email' => (string) ($user->email ?? '-'),
            'role' => $moduleRoleName ?: (optional($user->roles->first())->name ?? 'No Role Assigned'),
            'created_at' => $user->created_at?->toDateTimeString(),
            'created_at_text' => $user->created_at?->format('M d, Y h:i A') ?? '-',
            'is_active' => $resolvedStatus,
            'is_archived' => $user->deleted_at !== null,
        ];
    }

    private function buildPlatformRow(User $user): array
    {
        $displayName = trim((string) ($user->profile?->full_name ?? ''));
        $displayName = $displayName !== '' ? $displayName : (string) ($user->username ?? $user->email ?? 'User');

        $activeMemberships = $this->activeMemberships($user);
        $rolesByModule = $this->activeRolesByModule($user, $activeMemberships);

        $moduleChips = $activeMemberships
            ->map(fn (UserModule $membership): string => $this->moduleShortLabel($membership->module))
            ->filter()
            ->unique()
            ->values();

        $roleModuleSummaries = $rolesByModule
            ->map(function (Collection $roles, string $moduleId) use ($activeMemberships): ?array {
                $membership = $activeMemberships->firstWhere('module_id', $moduleId);

                if (! $membership) {
                    return null;
                }

                return [
                    'module' => $this->moduleShortLabel($membership->module),
                    'roles' => $roles->values()->all(),
                ];
            })
            ->filter()
            ->values();

        return [
            'id' => (string) $user->id,
            'display_name' => $displayName,
            'username' => (string) ($user->username ?? '-'),
            'email' => (string) ($user->email ?? '-'),
            'home_department_label' => $this->departmentLabel($user->primaryDepartment),
            'module_access_summary' => [
                'count' => $moduleChips->count(),
                'chips' => $moduleChips->take(3)->values()->all(),
                'extra_count' => max(0, $moduleChips->count() - 3),
                'empty' => $moduleChips->isEmpty(),
            ],
            'roles_by_module_summary' => [
                'entries' => $roleModuleSummaries->take(2)->all(),
                'extra_count' => max(0, $roleModuleSummaries->count() - 2),
                'empty' => $roleModuleSummaries->isEmpty(),
            ],
            'last_login_at' => $user->last_login_at?->toDateTimeString(),
            'last_login_at_text' => $user->last_login_at?->format('M d, Y h:i A') ?? 'Never',
            'created_at' => $user->created_at?->toDateTimeString(),
            'created_at_text' => $user->created_at?->format('M d, Y') ?? '-',
            'platform_status_label' => $this->platformStatusLabel($user),
            'platform_status_key' => $this->platformStatusKey($user),
            'is_active' => (bool) $user->is_active,
            'is_archived' => $user->deleted_at !== null,
        ];
    }

    private function activeMemberships(User $user): Collection
    {
        return $user->relationLoaded('userModules')
            ? $user->userModules->filter(fn (UserModule $membership): bool => (bool) $membership->is_active)->values()
            : collect();
    }

    private function activeRolesByModule(User $user, Collection $activeMemberships): Collection
    {
        $activeModuleIds = $activeMemberships
            ->map(fn (UserModule $membership): string => (string) $membership->module_id)
            ->filter()
            ->unique()
            ->values();

        if ($activeModuleIds->isEmpty() || ! $user->relationLoaded('moduleRoleAssignments')) {
            return collect();
        }

        return $user->moduleRoleAssignments
            ->filter(fn ($assignment): bool => $activeModuleIds->contains((string) $assignment->module_id))
            ->groupBy(fn ($assignment): string => (string) $assignment->module_id)
            ->map(fn (Collection $assignments): Collection => $assignments
                ->map(fn ($assignment): ?string => $assignment->role?->name)
                ->filter()
                ->unique()
                ->values());
    }

    private function moduleShortLabel(?Module $module): string
    {
        if (! $module) {
            return 'Module';
        }

        $code = strtoupper(trim((string) ($module->code ?? '')));
        $name = trim((string) ($module->name ?? ''));

        if ($code !== '') {
            return $code;
        }

        return $name !== '' ? $name : 'Module';
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
}
