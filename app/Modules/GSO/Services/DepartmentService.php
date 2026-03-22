<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\DepartmentDatatableRowBuilderInterface;
use App\Modules\GSO\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Modules\GSO\Services\Contracts\DepartmentServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DepartmentService implements DepartmentServiceInterface
{
    public function __construct(
        private readonly DepartmentRepositoryInterface $departments,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly DepartmentDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->departments->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (Department $department) => $this->datatableRowBuilder->build($department))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function optionsForSelect(): Collection
    {
        return $this->departments->activeOptions();
    }

    public function create(string $actorUserId, array $data): Department
    {
        return DB::transaction(function () use ($actorUserId, $data) {
            $department = $this->departments->create([
                'code' => trim((string) ($data['code'] ?? '')),
                'name' => trim((string) ($data['name'] ?? '')),
                'short_name' => $this->nullableString($data['short_name'] ?? null),
                'type' => $this->nullableString($data['type'] ?? null),
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
            ]);

            $this->auditLogs->record(
                action: 'gso.department.created',
                subject: $department,
                changesOld: [],
                changesNew: $department->only(['code', 'name', 'short_name', 'type', 'is_active']),
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO department created: ' . $this->departmentLabel($department),
                display: $this->buildCreatedDisplay($department),
            );

            return $department;
        });
    }

    public function update(string $actorUserId, string $departmentId, array $data): Department
    {
        return DB::transaction(function () use ($actorUserId, $departmentId, $data) {
            $department = $this->departments->findOrFail($departmentId);
            $before = $department->only(['code', 'name', 'short_name', 'type', 'is_active']);

            $department->code = trim((string) ($data['code'] ?? $department->code));
            $department->name = trim((string) ($data['name'] ?? $department->name));
            $department->short_name = $this->nullableString($data['short_name'] ?? $department->short_name);
            $department->type = $this->nullableString($data['type'] ?? $department->type);
            $department->is_active = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : (bool) $department->is_active;

            $department = $this->departments->save($department);
            $after = $department->only(['code', 'name', 'short_name', 'type', 'is_active']);

            $this->auditLogs->record(
                action: 'gso.department.updated',
                subject: $department,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO department updated: ' . $this->departmentLabel($department),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $department;
        });
    }

    public function delete(string $actorUserId, string $departmentId): void
    {
        DB::transaction(function () use ($actorUserId, $departmentId) {
            $department = $this->departments->findOrFail($departmentId);
            $before = $department->only(['code', 'name', 'short_name', 'type', 'is_active']);

            $this->departments->delete($department);

            $this->auditLogs->record(
                action: 'gso.department.deleted',
                subject: $department,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO department archived: ' . $this->departmentLabel($department),
                display: $this->buildLifecycleDisplay($department, 'Active Record', 'Archived'),
            );
        });
    }

    public function restore(string $actorUserId, string $departmentId): void
    {
        DB::transaction(function () use ($actorUserId, $departmentId) {
            $department = $this->departments->findOrFail($departmentId, true);

            if (! $department->trashed()) {
                return;
            }

            $deletedAt = $department->deleted_at?->toDateTimeString();
            $this->departments->restore($department);

            $this->auditLogs->record(
                action: 'gso.department.restored',
                subject: $department,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO department restored: ' . $this->departmentLabel($department),
                display: $this->buildLifecycleDisplay($department, 'Archived', 'Active Record'),
            );
        });
    }

    private function buildCreatedDisplay(Department $department): array
    {
        return [
            'summary' => 'Department created: ' . $this->departmentLabel($department),
            'subject_label' => $this->departmentLabel($department),
            'sections' => [[
                'title' => 'Department Details',
                'items' => [
                    ['label' => 'Code', 'before' => 'None', 'after' => $department->code],
                    ['label' => 'Name', 'before' => 'None', 'after' => $department->name],
                    ['label' => 'Short Name', 'before' => 'None', 'after' => $department->short_name ?? 'None'],
                    ['label' => 'Type', 'before' => 'None', 'after' => $department->type ?? 'None'],
                    ['label' => 'Record Status', 'before' => 'None', 'after' => $department->is_active ? 'Active' : 'Inactive'],
                ],
            ]],
        ];
    }

    private function buildUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'Department updated: ' . $this->departmentLabelFromValues(
                (string) ($after['code'] ?? $before['code'] ?? ''),
                (string) ($after['name'] ?? $before['name'] ?? ''),
            ),
            'subject_label' => $this->departmentLabelFromValues(
                (string) ($after['code'] ?? $before['code'] ?? ''),
                (string) ($after['name'] ?? $before['name'] ?? ''),
            ),
            'sections' => [[
                'title' => 'Department Details',
                'items' => [
                    ['label' => 'Code', 'before' => $before['code'] ?? 'None', 'after' => $after['code'] ?? 'None'],
                    ['label' => 'Name', 'before' => $before['name'] ?? 'None', 'after' => $after['name'] ?? 'None'],
                    ['label' => 'Short Name', 'before' => $before['short_name'] ?? 'None', 'after' => $after['short_name'] ?? 'None'],
                    ['label' => 'Type', 'before' => $before['type'] ?? 'None', 'after' => $after['type'] ?? 'None'],
                    [
                        'label' => 'Record Status',
                        'before' => array_key_exists('is_active', $before) ? ((bool) $before['is_active'] ? 'Active' : 'Inactive') : 'None',
                        'after' => array_key_exists('is_active', $after) ? ((bool) $after['is_active'] ? 'Active' : 'Inactive') : 'None',
                    ],
                ],
            ]],
        ];
    }

    private function buildLifecycleDisplay(Department $department, string $before, string $after): array
    {
        return [
            'summary' => 'Department ' . strtolower($after === 'Archived' ? 'archived' : 'restored') . ': ' . $this->departmentLabel($department),
            'subject_label' => $this->departmentLabel($department),
            'sections' => [[
                'title' => 'Department Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => $before, 'after' => $after],
                ],
            ]],
        ];
    }

    private function departmentLabel(Department $department): string
    {
        return $this->departmentLabelFromValues((string) $department->code, (string) $department->name);
    }

    private function departmentLabelFromValues(string $code, string $name): string
    {
        $code = trim($code);
        $name = trim($name);

        if ($code !== '' && $name !== '') {
            return "{$code} ({$name})";
        }

        if ($code !== '') {
            return $code;
        }

        if ($name !== '') {
            return $name;
        }

        return 'Department';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
