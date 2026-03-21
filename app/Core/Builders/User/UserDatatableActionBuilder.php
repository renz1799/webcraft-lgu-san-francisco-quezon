<?php

namespace App\Core\Builders\User;

use App\Core\Builders\Contracts\User\UserDatatableActionBuilderInterface;
use App\Core\Models\User;

class UserDatatableActionBuilder implements UserDatatableActionBuilderInterface
{
    public function build(User $user): array
    {
        $isArchived = $user->deleted_at !== null;

        return [
            'edit_url' => $isArchived ? null : route('access.users.edit', $user),
            'status_url' => $isArchived ? null : route('access.users.status.update', $user),
            'delete_url' => $isArchived ? null : route('access.users.destroy', $user),
            'restore_url' => $isArchived ? route('access.users.restore', $user) : null,
        ];
    }
}