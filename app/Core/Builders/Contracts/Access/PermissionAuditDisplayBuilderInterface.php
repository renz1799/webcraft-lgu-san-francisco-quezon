<?php

namespace App\Core\Builders\Contracts\Access;

use App\Core\Models\Permission;

interface PermissionAuditDisplayBuilderInterface
{
    public function buildCreatedDisplay(Permission $permission): array;

    public function buildUpdatedDisplay(array $before, array $after): array;

    public function buildDeletedDisplay(Permission $permission): array;

    public function buildRestoredDisplay(Permission $permission): array;
}
