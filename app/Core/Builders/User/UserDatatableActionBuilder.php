<?php

namespace App\Core\Builders\User;

use App\Core\Builders\Contracts\User\UserDatatableActionBuilderInterface;
use App\Core\Models\User;
use App\Core\Support\AdminRouteResolver;

class UserDatatableActionBuilder implements UserDatatableActionBuilderInterface
{
    public function __construct(
        private readonly ?AdminRouteResolver $adminRoutes = null,
    ) {}

    public function build(User $user): array
    {
        $isArchived = $user->deleted_at !== null;
        $adminRoutes = $this->adminRoutes ?? app(AdminRouteResolver::class);
        $moduleScoped = $adminRoutes->isModuleScoped();

        return [
            'access_overview_url' => (! $moduleScoped && ! $isArchived) ? $adminRoutes->route('access.users.access-overview', $user) : null,
            'edit_url' => ($moduleScoped && ! $isArchived) ? $adminRoutes->route('access.users.edit', $user) : null,
            'status_url' => $isArchived ? null : $adminRoutes->route('access.users.status.update', $user),
            'delete_url' => ($isArchived || $moduleScoped) ? null : $adminRoutes->route('access.users.destroy', $user),
            'restore_url' => ($isArchived && ! $moduleScoped) ? $adminRoutes->route('access.users.restore', $user) : null,
        ];
    }
}
