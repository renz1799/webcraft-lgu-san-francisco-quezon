<?php

namespace App\Core\Services\Auth;

use App\Core\Builders\Contracts\Auth\RegistrationRoleOptionsBuilderInterface;
use App\Core\Models\Role;
use App\Core\Services\Contracts\Auth\RegistrationOptionsServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Support\Collection;
use RuntimeException;

class RegistrationOptionsService implements RegistrationOptionsServiceInterface
{
    public function __construct(
        private readonly RegistrationRoleOptionsBuilderInterface $roleOptionsBuilder,
        private readonly CurrentContext $context,
    ) {}

    public function roles(): Collection
    {
        $moduleId = $this->requireModuleId();

        return Role::query()
            ->where('module_id', $moduleId)
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

    private function requireModuleId(): string
    {
        $moduleId = $this->context->moduleId();

        if (! $moduleId) {
            throw new RuntimeException('Current module context is not available.');
        }

        return $moduleId;
    }
}
