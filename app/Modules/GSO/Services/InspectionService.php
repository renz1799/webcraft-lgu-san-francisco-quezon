<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Core\Models\User;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\InspectionDatatableRowBuilderInterface;
use App\Modules\GSO\Models\Inspection;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Repositories\Contracts\InspectionRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InspectionServiceInterface;
use App\Modules\GSO\Support\InspectionStatuses;
use App\Modules\GSO\Support\InventoryConditions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InspectionService implements InspectionServiceInterface
{
    public function __construct(
        private readonly InspectionRepositoryInterface $inspections,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly InspectionDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->inspections->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (Inspection $inspection) => $this->datatableRowBuilder->build($inspection))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function getForEdit(string $inspectionId): array
    {
        $inspection = $this->inspections->findOrFail($inspectionId);

        return $this->datatableRowBuilder->build($inspection);
    }

    public function create(string $actorUserId, array $data): Inspection
    {
        return DB::transaction(function () use ($actorUserId, $data) {
            $payload = $this->normalizedPayload($data);
            $payload['inspector_user_id'] = $actorUserId;

            $this->assertRelationsExist($payload);
            $this->assertWorkflowRules($payload);

            $inspection = $this->inspections->create($payload);

            $this->auditLogs->record(
                action: 'gso.inspection.created',
                subject: $inspection,
                changesOld: [],
                changesNew: $inspection->only($this->auditFields()),
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO inspection created: ' . $this->inspectionLabel($inspection),
                display: $this->buildCreatedDisplay($inspection),
            );

            return $this->inspections->findOrFail((string) $inspection->id);
        });
    }

    public function update(string $actorUserId, string $inspectionId, array $data): Inspection
    {
        return DB::transaction(function () use ($actorUserId, $inspectionId, $data) {
            $inspection = $this->inspections->findOrFail($inspectionId);
            $before = $inspection->only($this->auditFields());

            $payload = $this->normalizedPayload($data, $inspection);
            $payload['inspector_user_id'] = (string) $inspection->inspector_user_id;

            $this->assertRelationsExist($payload);
            $this->assertWorkflowRules($payload);

            $inspection->fill($payload);
            $inspection = $this->inspections->save($inspection);
            $after = $inspection->only($this->auditFields());

            $this->auditLogs->record(
                action: 'gso.inspection.updated',
                subject: $inspection,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO inspection updated: ' . $this->inspectionLabel($inspection),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $this->inspections->findOrFail((string) $inspection->id);
        });
    }

    public function delete(string $actorUserId, string $inspectionId): void
    {
        DB::transaction(function () use ($actorUserId, $inspectionId) {
            $inspection = $this->inspections->findOrFail($inspectionId);
            $before = $inspection->only($this->auditFields());

            $this->inspections->delete($inspection);

            $this->auditLogs->record(
                action: 'gso.inspection.deleted',
                subject: $inspection,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO inspection archived: ' . $this->inspectionLabel($inspection),
                display: $this->buildLifecycleDisplay($inspection, 'Active Record', 'Archived'),
            );
        });
    }

    public function restore(string $actorUserId, string $inspectionId): void
    {
        DB::transaction(function () use ($actorUserId, $inspectionId) {
            $inspection = $this->inspections->findOrFail($inspectionId, true);

            if (! $inspection->trashed()) {
                return;
            }

            $deletedAt = $inspection->deleted_at?->toDateTimeString();
            $this->inspections->restore($inspection);

            $this->auditLogs->record(
                action: 'gso.inspection.restored',
                subject: $inspection,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO inspection restored: ' . $this->inspectionLabel($inspection),
                display: $this->buildLifecycleDisplay($inspection, 'Archived', 'Active Record'),
            );
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizedPayload(array $data, ?Inspection $inspection = null): array
    {
        $itemId = $this->nullableInput($data, 'item_id', $inspection?->item_id);
        $departmentId = $this->nullableInput($data, 'department_id', $inspection?->department_id);

        return [
            'reviewer_user_id' => $this->nullableInput($data, 'reviewer_user_id', $inspection?->reviewer_user_id),
            'status' => trim((string) ($data['status'] ?? $inspection?->status ?? InspectionStatuses::DRAFT)),
            'department_id' => $departmentId,
            'item_id' => $itemId,
            'office_department' => $this->resolveOfficeDepartmentValue(
                departmentId: $departmentId,
                fallback: array_key_exists('office_department', $data)
                    ? $data['office_department']
                    : $inspection?->office_department,
            ),
            'accountable_officer' => $this->nullableInput($data, 'accountable_officer', $inspection?->accountable_officer),
            'dv_number' => $this->nullableInput($data, 'dv_number', $inspection?->dv_number),
            'po_number' => $this->nullableInput($data, 'po_number', $inspection?->po_number),
            'observed_description' => $this->nullableInput($data, 'observed_description', $inspection?->observed_description),
            'item_name' => $this->resolveItemNameValue(
                itemId: $itemId,
                fallback: array_key_exists('item_name', $data)
                    ? $data['item_name']
                    : $inspection?->item_name,
            ),
            'brand' => $this->nullableInput($data, 'brand', $inspection?->brand),
            'model' => $this->nullableInput($data, 'model', $inspection?->model),
            'serial_number' => $this->nullableInput($data, 'serial_number', $inspection?->serial_number),
            'acquisition_cost' => array_key_exists('acquisition_cost', $data)
                ? (($data['acquisition_cost'] === null || $data['acquisition_cost'] === '') ? null : (float) $data['acquisition_cost'])
                : ($inspection?->acquisition_cost !== null ? (float) $inspection->acquisition_cost : null),
            'acquisition_date' => array_key_exists('acquisition_date', $data)
                ? $this->nullableDateString($data['acquisition_date'])
                : $inspection?->acquisition_date?->toDateString(),
            'quantity' => array_key_exists('quantity', $data)
                ? max(1, (int) $data['quantity'])
                : (int) ($inspection?->quantity ?? 1),
            'condition' => trim((string) ($data['condition'] ?? $inspection?->condition ?? InventoryConditions::GOOD)),
            'drive_folder_id' => $this->nullableInput($data, 'drive_folder_id', $inspection?->drive_folder_id),
            'remarks' => $this->nullableInput($data, 'remarks', $inspection?->remarks),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertRelationsExist(array $payload): void
    {
        $itemId = trim((string) ($payload['item_id'] ?? ''));
        if ($itemId !== '') {
            $item = Item::query()
                ->withTrashed()
                ->find($itemId);

            if (! $item || $item->trashed()) {
                throw ValidationException::withMessages([
                    'item_id' => ['Selected item is invalid.'],
                ]);
            }
        }

        $departmentId = trim((string) ($payload['department_id'] ?? ''));
        if ($departmentId !== '') {
            $department = Department::query()
                ->withTrashed()
                ->find($departmentId);

            if (! $department || $department->trashed()) {
                throw ValidationException::withMessages([
                    'department_id' => ['Selected department is invalid.'],
                ]);
            }
        }

        $reviewerUserId = trim((string) ($payload['reviewer_user_id'] ?? ''));
        if ($reviewerUserId !== '') {
            $reviewer = User::query()
                ->withTrashed()
                ->find($reviewerUserId);

            if (! $reviewer || $reviewer->trashed()) {
                throw ValidationException::withMessages([
                    'reviewer_user_id' => ['Selected reviewer is invalid.'],
                ]);
            }
        }

        $inspectorUserId = trim((string) ($payload['inspector_user_id'] ?? ''));
        if ($inspectorUserId !== '') {
            $inspector = User::query()
                ->withTrashed()
                ->find($inspectorUserId);

            if (! $inspector || $inspector->trashed()) {
                throw ValidationException::withMessages([
                    'inspector_user_id' => ['Selected inspector is invalid.'],
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertWorkflowRules(array $payload): void
    {
        $status = trim((string) ($payload['status'] ?? ''));
        $condition = trim((string) ($payload['condition'] ?? ''));
        $poNumber = trim((string) ($payload['po_number'] ?? ''));

        if (! in_array($status, InspectionStatuses::values(), true)) {
            throw ValidationException::withMessages([
                'status' => ['Status is invalid.'],
            ]);
        }

        if (! in_array($condition, InventoryConditions::values(), true)) {
            throw ValidationException::withMessages([
                'condition' => ['Condition is invalid.'],
            ]);
        }

        if ($status !== InspectionStatuses::DRAFT && $poNumber === '') {
            throw ValidationException::withMessages([
                'po_number' => ['PO number is required once an inspection leaves draft status.'],
            ]);
        }
    }

    private function resolveItemNameValue(?string $itemId, mixed $fallback): ?string
    {
        $fallbackValue = $this->nullableString($fallback);

        if ($fallbackValue !== null) {
            return $fallbackValue;
        }

        $itemId = trim((string) ($itemId ?? ''));
        if ($itemId === '') {
            return null;
        }

        $item = Item::query()
            ->withTrashed()
            ->select(['id', 'item_name'])
            ->find($itemId);

        return $this->nullableString($item?->item_name);
    }

    private function resolveOfficeDepartmentValue(?string $departmentId, mixed $fallback): ?string
    {
        $fallbackValue = $this->nullableString($fallback);

        if ($fallbackValue !== null) {
            return $fallbackValue;
        }

        $departmentId = trim((string) ($departmentId ?? ''));
        if ($departmentId === '') {
            return null;
        }

        $department = Department::query()
            ->withTrashed()
            ->select(['id', 'code', 'name'])
            ->find($departmentId);

        if (! $department) {
            return null;
        }

        $code = trim((string) $department->code);
        $name = trim((string) $department->name);

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : null);
    }

    /**
     * @return array<int, string>
     */
    private function auditFields(): array
    {
        return [
            'inspector_user_id',
            'reviewer_user_id',
            'status',
            'department_id',
            'item_id',
            'office_department',
            'accountable_officer',
            'dv_number',
            'po_number',
            'observed_description',
            'item_name',
            'brand',
            'model',
            'serial_number',
            'acquisition_cost',
            'acquisition_date',
            'quantity',
            'condition',
            'drive_folder_id',
            'remarks',
        ];
    }

    private function buildCreatedDisplay(Inspection $inspection): array
    {
        return [
            'summary' => 'Inspection created: ' . $this->inspectionLabel($inspection),
            'subject_label' => $this->inspectionLabel($inspection),
            'sections' => [[
                'title' => 'Inspection Details',
                'items' => [
                    ['label' => 'Status', 'before' => 'None', 'after' => $this->statusLabel($inspection->status)],
                    ['label' => 'Item', 'before' => 'None', 'after' => $this->displayValue($inspection->item_name)],
                    ['label' => 'Office / Department', 'before' => 'None', 'after' => $this->displayValue($inspection->office_department)],
                    ['label' => 'PO Number', 'before' => 'None', 'after' => $this->displayValue($inspection->po_number)],
                    ['label' => 'Condition', 'before' => 'None', 'after' => $this->conditionLabel($inspection->condition)],
                ],
            ]],
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    private function buildUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'Inspection updated: ' . $this->inspectionLabelFromValues($after),
            'subject_label' => $this->inspectionLabelFromValues($after),
            'sections' => [[
                'title' => 'Inspection Details',
                'items' => [
                    ['label' => 'Status', 'before' => $this->statusLabel($before['status'] ?? null), 'after' => $this->statusLabel($after['status'] ?? null)],
                    ['label' => 'Item', 'before' => $this->displayValue($before['item_name'] ?? null), 'after' => $this->displayValue($after['item_name'] ?? null)],
                    ['label' => 'Office / Department', 'before' => $this->displayValue($before['office_department'] ?? null), 'after' => $this->displayValue($after['office_department'] ?? null)],
                    ['label' => 'PO Number', 'before' => $this->displayValue($before['po_number'] ?? null), 'after' => $this->displayValue($after['po_number'] ?? null)],
                    ['label' => 'Condition', 'before' => $this->conditionLabel($before['condition'] ?? null), 'after' => $this->conditionLabel($after['condition'] ?? null)],
                ],
            ]],
        ];
    }

    private function buildLifecycleDisplay(Inspection $inspection, string $before, string $after): array
    {
        return [
            'summary' => 'Inspection ' . strtolower($after === 'Archived' ? 'archived' : 'restored') . ': ' . $this->inspectionLabel($inspection),
            'subject_label' => $this->inspectionLabel($inspection),
            'sections' => [[
                'title' => 'Inspection Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => $before, 'after' => $after],
                ],
            ]],
        ];
    }

    private function inspectionLabel(Inspection $inspection): string
    {
        return $this->inspectionLabelFromValues($inspection->toArray());
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function inspectionLabelFromValues(array $values): string
    {
        $itemName = trim((string) ($values['item_name'] ?? ''));
        $poNumber = trim((string) ($values['po_number'] ?? ''));

        if ($itemName !== '' && $poNumber !== '') {
            return "{$itemName} ({$poNumber})";
        }

        return $poNumber !== '' ? $poNumber : ($itemName !== '' ? $itemName : 'Inspection');
    }

    private function statusLabel(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return InspectionStatuses::labels()[$value] ?? 'None';
    }

    private function conditionLabel(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return InventoryConditions::labels()[$value] ?? 'None';
    }

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }

    private function nullableDateString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }

    private function nullableInput(array $data, string $key, mixed $existingValue = null): ?string
    {
        if (array_key_exists($key, $data)) {
            return $this->nullableString($data[$key]);
        }

        return $this->nullableString($existingValue);
    }
}
