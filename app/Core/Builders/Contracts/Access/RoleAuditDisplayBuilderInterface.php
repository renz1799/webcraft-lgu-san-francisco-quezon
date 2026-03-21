<?php

namespace App\Core\Builders\Contracts\Access;

use App\Core\Models\Role;

interface RoleAuditDisplayBuilderInterface
{
    public function buildCreatedDisplay(Role $role, array $permissions): array;

    public function buildUpdatedDisplay(array $before, array $after): array;

    public function buildDeletedDisplay(Role $role, array $snapshot): array;

    public function buildRestoredDisplay(Role $role, int $permissionCount): array;
}
