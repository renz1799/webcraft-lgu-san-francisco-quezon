<?php

namespace App\Services;

use App\Models\Permission;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Services\Contracts\PermissionServiceInterface;
use App\Services\Contracts\AuditLogServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class PermissionService implements PermissionServiceInterface
{
    public function __construct(
        private readonly PermissionRepositoryInterface $repo,
        private readonly AuditLogServiceInterface $audit,
    ) {}

    public function paginate(int $perPage = 30, string $trashed = 'none'): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $trashed);
    }

    public function create(array $data): Permission
    {
        $data['guard_name'] = $data['guard_name'] ?? 'web';
        $data['name']       = trim($data['name']);
        $data['page']       = trim($data['page']);

        $permission = $this->repo->create($data);

        $this->safeAudit(
            action: 'permission.created',
            permission: $permission,
            old: null,
            new: Arr::only($permission->toArray(), ['id','name','page','guard_name'])
        );

        return $permission;
    }

    public function delete(Permission $permission): void
    {
        $snapshot = Arr::only($permission->toArray(), ['id','name','page','guard_name']);

        $this->repo->delete($permission); // soft

        $this->safeAudit(
            action: 'permission.deleted',
            permission: $permission,
            old: $snapshot,
            new: null
        );
    }

    public function restore(string $id): bool
    {
        $ok = $this->repo->restore($id);
        if ($ok) {
            // Subject after restore:
            $restored = $this->repo->find($id, true);
            $this->safeAudit(
                action: 'permission.restored',
                permission: $restored ?? new Permission(['id' => $id]),
                old: null,
                new: $restored ? Arr::only($restored->toArray(), ['id','name','page','guard_name']) : null
            );
        }
        return $ok;
    }

    public function forceDelete(Permission $permission): void
    {
        $snapshot = Arr::only($permission->toArray(), ['id','name','page','guard_name']);

        $this->repo->forceDelete($permission);

        $this->safeAudit(
            action: 'permission.destroyed',
            permission: $permission,
            old: $snapshot,
            new: null
        );
    }

    private function safeAudit(string $action, Permission $permission, ?array $old, ?array $new): void
    {
        try {
            $this->audit->record(
                $action,           // string
                $permission,       // subject (Model)
                $old ?? [],        // changesOld
                $new ?? [],        // changesNew
                [
                    'ip' => request()->ip(),
                    'ua' => request()->userAgent(),
                ],
                null               // message
            );
        } catch (\Throwable $e) {
            Log::warning('audit.record_failed', [
                'action' => $action,
                'target' => ['type' => Permission::class, 'id' => $permission->id],
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
