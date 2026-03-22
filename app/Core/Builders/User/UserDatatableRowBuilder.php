<?php

namespace App\Core\Builders\User;

use App\Core\Builders\Contracts\User\UserDatatableRowBuilderInterface;
use App\Core\Models\User;

class UserDatatableRowBuilder implements UserDatatableRowBuilderInterface
{
    public function build(User $user): array
    {
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
}
