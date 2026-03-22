<?php

namespace App\Core\Services\Access;

use App\Core\Models\User;
use App\Core\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Core\Services\Contracts\Access\LoginLogServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Support\Collection;

class LoginLogService implements LoginLogServiceInterface
{
    public function __construct(
        private readonly LoginDetailRepositoryInterface $loginDetails,
        private readonly CurrentContext $context,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $currentModule = $this->context->module();
        $moduleId = $currentModule?->isPlatformContext()
            ? null
            : ($currentModule?->id ? (string) $currentModule->id : null);

        if (! $currentModule) {
            return [
                'data' => [],
                'last_page' => 1,
                'total' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
            ];
        }

        $filters = $params;
        unset($filters['page'], $filters['size']);

        return $this->loginDetails->datatable($moduleId, $filters, $page, $size);
    }

    public function recentForUser(User $user, int $limit = 4): Collection
    {
        $currentModule = $this->context->module();
        $moduleId = $currentModule?->id ? (string) $currentModule->id : null;

        if (! $moduleId) {
            return collect();
        }

        return $this->loginDetails->recentForUser($moduleId, (string) $user->id, $limit);
    }
}
