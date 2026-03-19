<?php

namespace App\Builders\User;

use App\Builders\Contracts\User\UserDatatableRowBuilderInterface;
use App\Models\User;

class UserDatatableRowBuilder implements UserDatatableRowBuilderInterface
{
    public function build(User $user): array
    {
        return [
            'id' => (string) $user->id,
            'username' => (string) ($user->username ?? '-'),
            'email' => (string) ($user->email ?? '-'),
            'role' => optional($user->roles->first())->name ?? 'No Role Assigned',
            'created_at' => $user->created_at?->toDateTimeString(),
            'created_at_text' => $user->created_at?->format('M d, Y h:i A') ?? '-',
            'is_active' => (bool) $user->is_active,
            'is_archived' => $user->deleted_at !== null,
        ];
    }
}