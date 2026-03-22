<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\AccountableOfficerDatatableRowBuilderInterface;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Repositories\Contracts\AccountableOfficerRepositoryInterface;
use App\Modules\GSO\Services\Contracts\AccountableOfficerServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountableOfficerService implements AccountableOfficerServiceInterface
{
    public function __construct(
        private readonly AccountableOfficerRepositoryInterface $accountableOfficers,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly AccountableOfficerDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->accountableOfficers->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (AccountableOfficer $accountableOfficer) => $this->datatableRowBuilder->build($accountableOfficer))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function suggest(string $query): array
    {
        return $this->accountableOfficers->suggest($query)
            ->map(fn (AccountableOfficer $accountableOfficer) => [
                'id' => (string) $accountableOfficer->id,
                'full_name' => (string) $accountableOfficer->full_name,
                'designation' => (string) ($accountableOfficer->designation ?? ''),
                'office' => (string) ($accountableOfficer->office ?? ''),
                'department_id' => $accountableOfficer->department_id ? (string) $accountableOfficer->department_id : null,
                'department_label' => $accountableOfficer->department
                    ? $this->resolveDepartmentLabel((string) $accountableOfficer->department->id)
                    : 'None',
            ])
            ->values()
            ->all();
    }

    public function create(string $actorUserId, array $data): AccountableOfficer
    {
        return DB::transaction(function () use ($actorUserId, $data) {
            $fullName = $this->cleanValue((string) ($data['full_name'] ?? ''));
            $normalizedName = $this->normalizeName($fullName);
            $existing = $this->accountableOfficers->findByNormalizedName($normalizedName, withTrashed: true);

            if ($existing) {
                $message = $existing->trashed()
                    ? 'An archived accountable officer with this name already exists. Restore it instead.'
                    : 'An accountable officer with this name already exists.';

                throw ValidationException::withMessages([
                    'full_name' => [$message],
                ]);
            }

            $accountableOfficer = $this->accountableOfficers->create([
                'full_name' => $fullName,
                'normalized_name' => $normalizedName,
                'designation' => $this->nullableString($data['designation'] ?? null),
                'office' => $this->nullableString($data['office'] ?? null),
                'department_id' => $this->nullableString($data['department_id'] ?? null),
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
            ]);

            $this->auditLogs->record(
                action: 'gso.accountable_officer.created',
                subject: $accountableOfficer,
                changesOld: [],
                changesNew: $accountableOfficer->only(['full_name', 'designation', 'office', 'department_id', 'is_active']),
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO accountable officer created: ' . $this->label($accountableOfficer),
                display: $this->buildCreatedDisplay($accountableOfficer),
            );

            return $accountableOfficer;
        });
    }

    public function update(string $actorUserId, string $accountableOfficerId, array $data): AccountableOfficer
    {
        return DB::transaction(function () use ($actorUserId, $accountableOfficerId, $data) {
            $accountableOfficer = $this->accountableOfficers->findOrFail($accountableOfficerId);
            $fullName = $this->cleanValue((string) ($data['full_name'] ?? $accountableOfficer->full_name));
            $normalizedName = $this->normalizeName($fullName);
            $duplicate = $this->accountableOfficers->findByNormalizedName($normalizedName, $accountableOfficerId, true);

            if ($duplicate) {
                $message = $duplicate->trashed()
                    ? 'An archived accountable officer with this name already exists. Restore it instead.'
                    : 'An accountable officer with this name already exists.';

                throw ValidationException::withMessages([
                    'full_name' => [$message],
                ]);
            }

            $before = $accountableOfficer->only(['full_name', 'designation', 'office', 'department_id', 'is_active']);

            $accountableOfficer->full_name = $fullName;
            $accountableOfficer->normalized_name = $normalizedName;
            $accountableOfficer->designation = $this->nullableString($data['designation'] ?? $accountableOfficer->designation);
            $accountableOfficer->office = $this->nullableString($data['office'] ?? $accountableOfficer->office);
            $accountableOfficer->department_id = $this->nullableString($data['department_id'] ?? $accountableOfficer->department_id);
            $accountableOfficer->is_active = array_key_exists('is_active', $data) ? (bool) $data['is_active'] : (bool) $accountableOfficer->is_active;

            $accountableOfficer = $this->accountableOfficers->save($accountableOfficer);
            $after = $accountableOfficer->only(['full_name', 'designation', 'office', 'department_id', 'is_active']);

            $this->auditLogs->record(
                action: 'gso.accountable_officer.updated',
                subject: $accountableOfficer,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO accountable officer updated: ' . $this->label($accountableOfficer),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $accountableOfficer;
        });
    }

    public function delete(string $actorUserId, string $accountableOfficerId): void
    {
        DB::transaction(function () use ($actorUserId, $accountableOfficerId) {
            $accountableOfficer = $this->accountableOfficers->findOrFail($accountableOfficerId);
            $before = $accountableOfficer->only(['full_name', 'designation', 'office', 'department_id', 'is_active']);

            $this->accountableOfficers->delete($accountableOfficer);

            $this->auditLogs->record(
                action: 'gso.accountable_officer.deleted',
                subject: $accountableOfficer,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO accountable officer archived: ' . $this->label($accountableOfficer),
                display: $this->buildLifecycleDisplay($accountableOfficer, 'Active Record', 'Archived'),
            );
        });
    }

    public function restore(string $actorUserId, string $accountableOfficerId): void
    {
        DB::transaction(function () use ($actorUserId, $accountableOfficerId) {
            $accountableOfficer = $this->accountableOfficers->findOrFail($accountableOfficerId, true);

            if (! $accountableOfficer->trashed()) {
                return;
            }

            $deletedAt = $accountableOfficer->deleted_at?->toDateTimeString();
            $this->accountableOfficers->restore($accountableOfficer);

            $this->auditLogs->record(
                action: 'gso.accountable_officer.restored',
                subject: $accountableOfficer,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO accountable officer restored: ' . $this->label($accountableOfficer),
                display: $this->buildLifecycleDisplay($accountableOfficer, 'Archived', 'Active Record'),
            );
        });
    }

    private function buildCreatedDisplay(AccountableOfficer $accountableOfficer): array
    {
        return [
            'summary' => 'Accountable officer created: ' . $this->label($accountableOfficer),
            'subject_label' => $this->label($accountableOfficer),
            'sections' => [[
                'title' => 'Accountable Officer Details',
                'items' => [
                    ['label' => 'Full Name', 'before' => 'None', 'after' => $accountableOfficer->full_name],
                    ['label' => 'Designation', 'before' => 'None', 'after' => $accountableOfficer->designation ?? 'None'],
                    ['label' => 'Office', 'before' => 'None', 'after' => $accountableOfficer->office ?? 'None'],
                    ['label' => 'Department', 'before' => 'None', 'after' => $this->resolveDepartmentLabel($accountableOfficer->department_id)],
                    ['label' => 'Record Status', 'before' => 'None', 'after' => $accountableOfficer->is_active ? 'Active' : 'Inactive'],
                ],
            ]],
        ];
    }

    private function buildUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'Accountable officer updated: ' . $this->labelFromValues(
                (string) ($after['full_name'] ?? $before['full_name'] ?? ''),
                (string) ($after['designation'] ?? $before['designation'] ?? ''),
            ),
            'subject_label' => $this->labelFromValues(
                (string) ($after['full_name'] ?? $before['full_name'] ?? ''),
                (string) ($after['designation'] ?? $before['designation'] ?? ''),
            ),
            'sections' => [[
                'title' => 'Accountable Officer Details',
                'items' => [
                    ['label' => 'Full Name', 'before' => $before['full_name'] ?? 'None', 'after' => $after['full_name'] ?? 'None'],
                    ['label' => 'Designation', 'before' => $before['designation'] ?? 'None', 'after' => $after['designation'] ?? 'None'],
                    ['label' => 'Office', 'before' => $before['office'] ?? 'None', 'after' => $after['office'] ?? 'None'],
                    ['label' => 'Department', 'before' => $this->resolveDepartmentLabel($before['department_id'] ?? null), 'after' => $this->resolveDepartmentLabel($after['department_id'] ?? null)],
                    [
                        'label' => 'Record Status',
                        'before' => array_key_exists('is_active', $before) ? ((bool) $before['is_active'] ? 'Active' : 'Inactive') : 'None',
                        'after' => array_key_exists('is_active', $after) ? ((bool) $after['is_active'] ? 'Active' : 'Inactive') : 'None',
                    ],
                ],
            ]],
        ];
    }

    private function buildLifecycleDisplay(AccountableOfficer $accountableOfficer, string $before, string $after): array
    {
        return [
            'summary' => 'Accountable officer ' . strtolower($after === 'Archived' ? 'archived' : 'restored') . ': ' . $this->label($accountableOfficer),
            'subject_label' => $this->label($accountableOfficer),
            'sections' => [[
                'title' => 'Accountable Officer Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => $before, 'after' => $after],
                ],
            ]],
        ];
    }

    private function label(AccountableOfficer $accountableOfficer): string
    {
        return $this->labelFromValues((string) $accountableOfficer->full_name, (string) ($accountableOfficer->designation ?? ''));
    }

    private function labelFromValues(string $fullName, string $designation): string
    {
        $fullName = trim($fullName);
        $designation = trim($designation);

        if ($fullName !== '' && $designation !== '') {
            return "{$fullName} ({$designation})";
        }

        if ($fullName !== '') {
            return $fullName;
        }

        if ($designation !== '') {
            return $designation;
        }

        return 'Accountable Officer';
    }

    private function resolveDepartmentLabel(?string $departmentId): string
    {
        $departmentId = trim((string) ($departmentId ?? ''));

        if ($departmentId === '') {
            return 'None';
        }

        $department = Department::query()
            ->withTrashed()
            ->select(['id', 'code', 'name'])
            ->find($departmentId);

        if (! $department) {
            return 'Unknown Department';
        }

        $code = trim((string) $department->code);
        $name = trim((string) $department->name);

        if ($code !== '' && $name !== '') {
            return "{$code} ({$name})";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Department');
    }

    private function normalizeName(string $value): string
    {
        return mb_strtolower($this->cleanValue($value));
    }

    private function cleanValue(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim($value)) ?? '';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = $this->cleanValue((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
