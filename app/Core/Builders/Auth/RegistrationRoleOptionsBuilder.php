<?php

namespace App\Core\Builders\Auth;

use App\Core\Builders\Contracts\Auth\RegistrationRoleOptionsBuilderInterface;
use App\Core\Models\Role;
use Illuminate\Support\Collection;

class RegistrationRoleOptionsBuilder implements RegistrationRoleOptionsBuilderInterface
{
    public function build(Collection $roles): array
    {
        return $roles
            ->map(fn (Role $role) => [
                'id' => (string) $role->id,
                'name' => (string) $role->name,
            ])
            ->values()
            ->all();
    }
}