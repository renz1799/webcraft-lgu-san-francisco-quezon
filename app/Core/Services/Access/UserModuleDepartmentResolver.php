<?php

namespace App\Core\Services\Access;

use App\Core\Models\UserModule;
use App\Core\Services\Contracts\Access\UserModuleDepartmentResolverInterface;
use App\Core\Support\CurrentContext;

class UserModuleDepartmentResolver implements UserModuleDepartmentResolverInterface
{
    public function __construct(
        private readonly CurrentContext $context,
    ) {}

    public function resolveForUser(?string $userId, ?string $moduleId = null): ?string
    {
        $userId = trim((string) $userId);
        $moduleId = trim((string) ($moduleId ?: $this->context->moduleId()));

        if ($userId === '' || $moduleId === '') {
            return null;
        }

        $baseQuery = UserModule::query()
            ->where('user_id', $userId)
            ->where('module_id', $moduleId)
            ->where('is_active', true);

        $departmentId = (clone $baseQuery)
            ->whereNotNull('department_id')
            ->orderByDesc('granted_at')
            ->orderByDesc('updated_at')
            ->value('department_id');

        if ($departmentId !== null) {
            return $departmentId;
        }

        return (clone $baseQuery)
            ->orderByDesc('granted_at')
            ->orderByDesc('updated_at')
            ->value('department_id');
    }
}
