<?php

namespace App\Builders\User;

use App\Builders\Contracts\User\UserDatatableRowBuilderInterface;
use App\Models\User;

class UserDatatableRowBuilder implements UserDatatableRowBuilderInterface
{
    public function build(User $user): array
    {
        $isArchived = $user->deleted_at !== null;

        return [
            'id' => (string) $user->id,
            'username' => (string) ($user->username ?? '-'),
            'email' => (string) ($user->email ?? '-'),
            'role' => optional($user->roles->first())->name ?? 'No Role Assigned',
            'created_at' => $user->created_at?->toDateTimeString(),
            'created_at_text' => $user->created_at?->format('M d, Y h:i A') ?? '-',
            'is_active' => (bool) $user->is_active,
            'is_archived' => $isArchived,
            'edit_url' => $isArchived ? null : route('access.users.edit', $user),
            'status_url' => $isArchived ? null : route('access.users.status.update', $user),
            'delete_url' => $isArchived ? null : route('access.users.destroy', $user),
            'restore_url' => $isArchived ? route('access.users.restore', $user) : null,
        ];
    }
}