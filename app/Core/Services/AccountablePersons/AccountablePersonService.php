<?php

namespace App\Core\Services\AccountablePersons;

use App\Core\Builders\Contracts\AccountablePersons\AccountablePersonDatatableRowBuilderInterface;
use App\Core\Models\AccountablePerson;
use App\Core\Models\Department;
use App\Core\Repositories\Contracts\AccountablePersonRepositoryInterface;
use App\Core\Services\Contracts\AccountablePersons\AccountablePersonServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountablePersonService implements AccountablePersonServiceInterface
{
    public function __construct(
        private readonly AccountablePersonRepositoryInterface $accountablePersons,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly AccountablePersonDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->accountablePersons->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (AccountablePerson $accountablePerson) => $this->datatableRowBuilder->build($accountablePerson))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function suggest(string $query): array
    {
        return $this->accountablePersons->suggest($query)
            ->map(fn (AccountablePerson $accountablePerson) => $this->mapPerson($accountablePerson))
            ->values()
            ->all();
    }

    public function createOrResolve(string $actorUserId, array $data): array
    {
        return DB::transaction(function () use ($actorUserId, $data): array {
            $fullName = $this->cleanValue((string) ($data['full_name'] ?? ''));
            $normalizedName = $this->normalizeName($fullName);
            $designation = $this->nullableString($data['designation'] ?? null);
            $office = $this->nullableString($data['office'] ?? null);
            $departmentId = $this->nullableString($data['department_id'] ?? null);

            $existing = $this->accountablePersons->findByNormalizedName($normalizedName, withTrashed: true);

            if (! $existing) {
                $created = $this->accountablePersons->create([
                    'full_name' => $fullName,
                    'normalized_name' => $normalizedName,
                    'designation' => $designation,
                    'office' => $office,
                    'department_id' => $departmentId,
                    'is_active' => true,
                ]);

                $this->recordAudit(
                    actorUserId: $actorUserId,
                    action: 'accountable_person.created',
                    accountablePerson: $created,
                    before: [],
                    after: $created->only(['full_name', 'designation', 'office', 'department_id', 'is_active']),
                    message: 'Accountable person created: ' . $this->label($created),
                    display: $this->buildCreatedDisplay($created),
                );

                return [
                    'officer' => $this->mapPerson($created),
                    'created' => true,
                    'restored' => false,
                    'reused' => false,
                ];
            }

            $before = $existing->only(['full_name', 'designation', 'office', 'department_id', 'is_active']);
            $restored = false;
            $changed = false;

            if ($existing->trashed()) {
                $this->accountablePersons->restore($existing);
                $restored = true;
                $changed = true;
            }

            if ($this->nullableString($existing->designation) === null && $designation !== null) {
                $existing->designation = $designation;
                $changed = true;
            }

            if ($this->nullableString($existing->office) === null && $office !== null) {
                $existing->office = $office;
                $changed = true;
            }

            if ($this->nullableString($existing->department_id) === null && $departmentId !== null) {
                $existing->department_id = $departmentId;
                $changed = true;
            }

            if (! $existing->is_active) {
                $existing->is_active = true;
                $changed = true;
            }

            if ($changed) {
                $existing = $this->accountablePersons->save($existing);
                $after = $existing->only(['full_name', 'designation', 'office', 'department_id', 'is_active']);

                $this->recordAudit(
                    actorUserId: $actorUserId,
                    action: $restored ? 'accountable_person.restored' : 'accountable_person.reused',
                    accountablePerson: $existing,
                    before: $before,
                    after: $after,
                    message: $restored
                        ? 'Accountable person restored and reused: ' . $this->label($existing)
                        : 'Accountable person reused: ' . $this->label($existing),
                    display: $this->buildUpdatedDisplay($before, $after),
                );
            } else {
                $existing = $existing->fresh(['department']) ?? $existing->load('department');
            }

            return [
                'officer' => $this->mapPerson($existing),
                'created' => false,
                'restored' => $restored,
                'reused' => true,
            ];
        });
    }

    public function create(string $actorUserId, array $data): AccountablePerson
    {
        return DB::transaction(function () use ($actorUserId, $data) {
            $fullName = $this->cleanValue((string) ($data['full_name'] ?? ''));
            $normalizedName = $this->normalizeName($fullName);
            $existing = $this->accountablePersons->findByNormalizedName($normalizedName, withTrashed: true);

            if ($existing) {
                $message = $existing->trashed()
                    ? 'An archived accountable person with this name already exists. Restore it instead.'
                    : 'An accountable person with this name already exists.';

                throw ValidationException::withMessages([
                    'full_name' => [$message],
                ]);
            }

            $accountablePerson = $this->accountablePersons->create([
                'full_name' => $fullName,
                'normalized_name' => $normalizedName,
                'designation' => $this->nullableString($data['designation'] ?? null),
                'office' => $this->nullableString($data['office'] ?? null),
                'department_id' => $this->nullableString($data['department_id'] ?? null),
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
            ]);

            $this->recordAudit(
                actorUserId: $actorUserId,
                action: 'accountable_person.created',
                accountablePerson: $accountablePerson,
                before: [],
                after: $accountablePerson->only(['full_name', 'designation', 'office', 'department_id', 'is_active']),
                message: 'Accountable person created: ' . $this->label($accountablePerson),
                display: $this->buildCreatedDisplay($accountablePerson),
            );

            return $accountablePerson;
        });
    }

    public function update(string $actorUserId, string $accountablePersonId, array $data): AccountablePerson
    {
        return DB::transaction(function () use ($actorUserId, $accountablePersonId, $data) {
            $accountablePerson = $this->accountablePersons->findOrFail($accountablePersonId);
            $fullName = $this->cleanValue((string) ($data['full_name'] ?? $accountablePerson->full_name));
            $normalizedName = $this->normalizeName($fullName);
            $duplicate = $this->accountablePersons->findByNormalizedName($normalizedName, $accountablePersonId, true);

            if ($duplicate) {
                $message = $duplicate->trashed()
                    ? 'An archived accountable person with this name already exists. Restore it instead.'
                    : 'An accountable person with this name already exists.';

                throw ValidationException::withMessages([
                    'full_name' => [$message],
                ]);
            }

            $before = $accountablePerson->only(['full_name', 'designation', 'office', 'department_id', 'is_active']);

            $accountablePerson->full_name = $fullName;
            $accountablePerson->normalized_name = $normalizedName;
            $accountablePerson->designation = $this->nullableString($data['designation'] ?? $accountablePerson->designation);
            $accountablePerson->office = $this->nullableString($data['office'] ?? $accountablePerson->office);
            $accountablePerson->department_id = $this->nullableString($data['department_id'] ?? $accountablePerson->department_id);
            $accountablePerson->is_active = array_key_exists('is_active', $data)
                ? (bool) $data['is_active']
                : (bool) $accountablePerson->is_active;

            $accountablePerson = $this->accountablePersons->save($accountablePerson);
            $after = $accountablePerson->only(['full_name', 'designation', 'office', 'department_id', 'is_active']);

            $this->recordAudit(
                actorUserId: $actorUserId,
                action: 'accountable_person.updated',
                accountablePerson: $accountablePerson,
                before: $before,
                after: $after,
                message: 'Accountable person updated: ' . $this->label($accountablePerson),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $accountablePerson;
        });
    }

    public function delete(string $actorUserId, string $accountablePersonId): void
    {
        DB::transaction(function () use ($actorUserId, $accountablePersonId) {
            $accountablePerson = $this->accountablePersons->findOrFail($accountablePersonId);
            $before = $accountablePerson->only(['full_name', 'designation', 'office', 'department_id', 'is_active']);

            $this->accountablePersons->delete($accountablePerson);

            $this->recordAudit(
                actorUserId: $actorUserId,
                action: 'accountable_person.deleted',
                accountablePerson: $accountablePerson,
                before: $before,
                after: ['deleted_at' => now()->toDateTimeString()],
                message: 'Accountable person archived: ' . $this->label($accountablePerson),
                display: $this->buildLifecycleDisplay($accountablePerson, 'Active Record', 'Archived'),
            );
        });
    }

    public function restore(string $actorUserId, string $accountablePersonId): void
    {
        DB::transaction(function () use ($actorUserId, $accountablePersonId) {
            $accountablePerson = $this->accountablePersons->findOrFail($accountablePersonId, true);

            if (! $accountablePerson->trashed()) {
                return;
            }

            $deletedAt = $accountablePerson->deleted_at?->toDateTimeString();
            $this->accountablePersons->restore($accountablePerson);

            $this->recordAudit(
                actorUserId: $actorUserId,
                action: 'accountable_person.restored',
                accountablePerson: $accountablePerson,
                before: ['deleted_at' => $deletedAt],
                after: ['restored_at' => now()->toDateTimeString()],
                message: 'Accountable person restored: ' . $this->label($accountablePerson),
                display: $this->buildLifecycleDisplay($accountablePerson, 'Archived', 'Active Record'),
            );
        });
    }

    private function recordAudit(
        string $actorUserId,
        string $action,
        AccountablePerson $accountablePerson,
        array $before,
        array $after,
        string $message,
        array $display,
    ): void {
        $this->auditLogs->record(
            action: $action,
            subject: $accountablePerson,
            changesOld: $before,
            changesNew: $after,
            meta: ['actor_user_id' => $actorUserId],
            message: $message,
            display: $display,
        );
    }

    private function buildCreatedDisplay(AccountablePerson $accountablePerson): array
    {
        return [
            'summary' => 'Accountable person created: ' . $this->label($accountablePerson),
            'subject_label' => $this->label($accountablePerson),
            'sections' => [[
                'title' => 'Accountable Person Details',
                'items' => [
                    ['label' => 'Full Name', 'before' => 'None', 'after' => $accountablePerson->full_name],
                    ['label' => 'Designation', 'before' => 'None', 'after' => $accountablePerson->designation ?? 'None'],
                    ['label' => 'Office', 'before' => 'None', 'after' => $accountablePerson->office ?? 'None'],
                    ['label' => 'Department', 'before' => 'None', 'after' => $this->resolveDepartmentLabel($accountablePerson->department_id)],
                    ['label' => 'Record Status', 'before' => 'None', 'after' => $accountablePerson->is_active ? 'Active' : 'Inactive'],
                ],
            ]],
        ];
    }

    private function buildUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'Accountable person updated: ' . $this->labelFromValues(
                (string) ($after['full_name'] ?? $before['full_name'] ?? ''),
                (string) ($after['designation'] ?? $before['designation'] ?? ''),
            ),
            'subject_label' => $this->labelFromValues(
                (string) ($after['full_name'] ?? $before['full_name'] ?? ''),
                (string) ($after['designation'] ?? $before['designation'] ?? ''),
            ),
            'sections' => [[
                'title' => 'Accountable Person Details',
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

    private function buildLifecycleDisplay(AccountablePerson $accountablePerson, string $before, string $after): array
    {
        return [
            'summary' => 'Accountable person ' . strtolower($after === 'Archived' ? 'archived' : 'restored') . ': ' . $this->label($accountablePerson),
            'subject_label' => $this->label($accountablePerson),
            'sections' => [[
                'title' => 'Accountable Person Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => $before, 'after' => $after],
                ],
            ]],
        ];
    }

    private function label(AccountablePerson $accountablePerson): string
    {
        return $this->labelFromValues((string) $accountablePerson->full_name, (string) ($accountablePerson->designation ?? ''));
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPerson(AccountablePerson $accountablePerson): array
    {
        return [
            'id' => (string) $accountablePerson->id,
            'full_name' => (string) $accountablePerson->full_name,
            'designation' => (string) ($accountablePerson->designation ?? ''),
            'office' => (string) ($accountablePerson->office ?? ''),
            'department_id' => $accountablePerson->department_id ? (string) $accountablePerson->department_id : null,
            'department_name' => (string) ($accountablePerson->department?->name ?? ''),
            'department_label' => $accountablePerson->department
                ? $this->resolveDepartmentLabel((string) $accountablePerson->department->id)
                : 'None',
            'is_active' => (bool) $accountablePerson->is_active,
        ];
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

        return 'Accountable Person';
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
