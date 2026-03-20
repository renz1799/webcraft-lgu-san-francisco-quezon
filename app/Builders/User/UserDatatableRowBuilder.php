<?php

namespace App\Builders\User;

use App\Builders\Contracts\User\UserDatatableRowBuilderInterface;
use App\Models\User;

class UserDatatableRowBuilder implements UserDatatableRowBuilderInterface
{
    public function build(User $user): array
    {
        $moduleRoleName = $user->getAttribute('current_module_role_name');

        if (! is_string($moduleRoleName) || trim($moduleRoleName) === '') {
            $moduleRoleName = $user->relationLoaded('moduleRoleAssignments')
                ? ($user->moduleRoleAssignments->first()?->role?->name ?? null)
                : null;
        }

        return [
            'id' => (string) $user->id,
            'username' => (string) ($user->username ?? '-'),
            'email' => (string) ($user->email ?? '-'),
            'role' => $moduleRoleName ?: (optional($user->roles->first())->name ?? 'No Role Assigned'),
            'created_at' => $user->created_at?->toDateTimeString(),
            'created_at_text' => $user->created_at?->format('M d, Y h:i A') ?? '-',
            'is_active' => (bool) $user->is_active,
            'is_archived' => $user->deleted_at !== null,
        ];
    }
}
