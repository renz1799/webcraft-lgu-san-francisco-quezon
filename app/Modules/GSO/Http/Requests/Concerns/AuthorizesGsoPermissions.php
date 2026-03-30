<?php

namespace App\Modules\GSO\Http\Requests\Concerns;

use App\Core\Support\AdminContextAuthorizer;

trait AuthorizesGsoPermissions
{
    protected function allowsGsoPermission(string $permission): bool
    {
        $user = $this->user();

        return (bool) $user
            && app(AdminContextAuthorizer::class)->allowsPermission($user, $permission);
    }

    protected function allowsAnyGsoPermission(array|string $permissions): bool
    {
        $user = $this->user();

        return (bool) $user
            && app(AdminContextAuthorizer::class)->allowsAnyPermission($user, $permissions);
    }
}
