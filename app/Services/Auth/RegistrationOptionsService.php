<?php

namespace App\Services\Auth;

use App\Builders\Contracts\Auth\RegistrationRoleOptionsBuilderInterface;
use App\Models\Role;
use App\Services\Contracts\Auth\RegistrationOptionsServiceInterface;
use Illuminate\Support\Collection;

class RegistrationOptionsService implements RegistrationOptionsServiceInterface
{
    public function __construct(
        private readonly RegistrationRoleOptionsBuilderInterface $roleOptionsBuilder,
    ) {}

    public function roles(): Collection
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->whereNull('deleted_at')
            ->where('name', '!=', 'Administrator')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function roleOptions(): array
    {
        return $this->roleOptionsBuilder->build($this->roles());
    }
}