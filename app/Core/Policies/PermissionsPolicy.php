<?php

namespace App\Core\Policies;

use App\Core\Models\User;
use App\Core\Support\AdminContextAuthorizer;

class PermissionsPolicy
{
    public function view(User $user, User $targetUser)
    {
        return $this->authorizer()->allowsAnyPermission($user, [
            'permissions.view',
            'access.permissions.manage',
        ]);
    }

    public function modify(User $user, User $targetUser)
    {
        return $this->authorizer()->allowsAnyPermission($user, [
            'permissions.update',
            'access.permissions.manage',
        ]);
    }

    private function authorizer(): AdminContextAuthorizer
    {
        return app(AdminContextAuthorizer::class);
    }
}
