<?php

namespace App\Builders\Contracts\Access;

use App\Models\Permission;

interface PermissionAuditDisplayBuilderInterface
{
    public function buildCreatedDisplay(Permission $permission): array;

    public function buildUpdatedDisplay(array $before, array $after): array;

    public function buildDeletedDisplay(Permission $permission): array;

    public function buildRestoredDisplay(Permission $permission): array;
}
