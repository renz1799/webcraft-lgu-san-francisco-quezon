<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\InventoryItemDatatableRowBuilderInterface;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemServiceInterface;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryItemService implements InventoryItemServiceInterface
{
    public function __construct(
        private readonly InventoryItemRepositoryInterface $inventoryItems,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly InventoryItemDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->inventoryItems->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (InventoryItem $inventoryItem) => $this->datatableRowBuilder->build($inventoryItem))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function getForEdit(string $inventoryItemId): array
    {
        $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId);

        return $this->datatableRowBuilder->build($inventoryItem);
    }

    public function create(string $actorUserId, array $data): InventoryItem
    {
        return DB::transaction(function () use ($actorUserId, $data) {
            $payload = $this->normalizedPayload($data);
            $item = $this->assertItemIsValid($payload['item_id']);
            $this->assertLifecycleRules($payload, $item);

            $inventoryItem = $this->inventoryItems->create($payload);

            $this->auditLogs->record(
                action: 'gso.inventory-item.created',
                subject: $inventoryItem,
                changesOld: [],
                changesNew: $inventoryItem->only($this->auditFields()),
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO inventory item created: ' . $this->inventoryItemLabel($inventoryItem),
                display: $this->buildCreatedDisplay($inventoryItem),
            );

            return $this->inventoryItems->findOrFail((string) $inventoryItem->id);
        });
    }

    public function update(string $actorUserId, string $inventoryItemId, array $data): InventoryItem
    {
        return DB::transaction(function () use ($actorUserId, $inventoryItemId, $data) {
            $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId);
            $before = $inventoryItem->only($this->auditFields());

            $payload = $this->normalizedPayload($data, $inventoryItem);
            $item = $this->assertItemIsValid($payload['item_id']);
            $this->assertLifecycleRules($payload, $item);

            $inventoryItem->fill($payload);
            $inventoryItem = $this->inventoryItems->save($inventoryItem);
            $after = $inventoryItem->only($this->auditFields());

            $this->auditLogs->record(
                action: 'gso.inventory-item.updated',
                subject: $inventoryItem,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO inventory item updated: ' . $this->inventoryItemLabel($inventoryItem),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $this->inventoryItems->findOrFail((string) $inventoryItem->id);
        });
    }

    public function delete(string $actorUserId, string $inventoryItemId): void
    {
        DB::transaction(function () use ($actorUserId, $inventoryItemId) {
            $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId);
            $before = $inventoryItem->only($this->auditFields());

            $this->inventoryItems->delete($inventoryItem);

            $this->auditLogs->record(
                action: 'gso.inventory-item.deleted',
                subject: $inventoryItem,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO inventory item archived: ' . $this->inventoryItemLabel($inventoryItem),
                display: $this->buildLifecycleDisplay($inventoryItem, 'Active Record', 'Archived'),
            );
        });
    }

    public function restore(string $actorUserId, string $inventoryItemId): void
    {
        DB::transaction(function () use ($actorUserId, $inventoryItemId) {
            $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId, true);

            if (! $inventoryItem->trashed()) {
                return;
            }

            $deletedAt = $inventoryItem->deleted_at?->toDateTimeString();
            $this->inventoryItems->restore($inventoryItem);

            $this->auditLogs->record(
                action: 'gso.inventory-item.restored',
                subject: $inventoryItem,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO inventory item restored: ' . $this->inventoryItemLabel($inventoryItem),
                display: $this->buildLifecycleDisplay($inventoryItem, 'Archived', 'Active Record'),
            );
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizedPayload(array $data, ?InventoryItem $inventoryItem = null): array
    {
        $accountableOfficerId = $this->nullableInput($data, 'accountable_officer_id', $inventoryItem?->accountable_officer_id);

        if (array_key_exists('accountable_officer', $data) && ! array_key_exists('accountable_officer_id', $data)) {
            $accountableOfficerId = null;
        }

        return [
            'item_id' => trim((string) ($data['item_id'] ?? $inventoryItem?->item_id ?? '')),
            'air_item_unit_id' => $this->nullableInput($data, 'air_item_unit_id', $inventoryItem?->air_item_unit_id),
            'department_id' => trim((string) ($data['department_id'] ?? $inventoryItem?->department_id ?? '')),
            'fund_source_id' => $this->nullableInput($data, 'fund_source_id', $inventoryItem?->fund_source_id),
            'property_number' => $this->nullableInput($data, 'property_number', $inventoryItem?->property_number),
            'acquisition_date' => trim((string) ($data['acquisition_date'] ?? $inventoryItem?->acquisition_date?->toDateString() ?? '')),
            'acquisition_cost' => array_key_exists('acquisition_cost', $data)
                ? (float) $data['acquisition_cost']
                : (float) ($inventoryItem?->acquisition_cost ?? 0),
            'description' => $this->nullableInput($data, 'description', $inventoryItem?->description),
            'quantity' => array_key_exists('quantity', $data)
                ? max(1, (int) $data['quantity'])
                : (int) ($inventoryItem?->quantity ?? 1),
            'unit' => $this->nullableInput($data, 'unit', $inventoryItem?->unit),
            'stock_number' => $this->nullableInput($data, 'stock_number', $inventoryItem?->stock_number),
            'service_life' => array_key_exists('service_life', $data)
                ? (($data['service_life'] === null || $data['service_life'] === '') ? null : max(0, (int) $data['service_life']))
                : $inventoryItem?->service_life,
            'is_ics' => array_key_exists('is_ics', $data)
                ? (bool) $data['is_ics']
                : (bool) ($inventoryItem?->is_ics ?? false),
            'accountable_officer_id' => $accountableOfficerId,
            'accountable_officer' => $this->resolveAccountableOfficerName(
                accountableOfficerId: $accountableOfficerId,
                fallbackName: array_key_exists('accountable_officer', $data)
                    ? $data['accountable_officer']
                    : $inventoryItem?->accountable_officer,
            ),
            'custody_state' => trim((string) ($data['custody_state'] ?? $inventoryItem?->custody_state ?? InventoryCustodyStates::POOL)),
            'status' => trim((string) ($data['status'] ?? $inventoryItem?->status ?? InventoryStatuses::SERVICEABLE)),
            'condition' => trim((string) ($data['condition'] ?? $inventoryItem?->condition ?? InventoryConditions::GOOD)),
            'brand' => $this->nullableInput($data, 'brand', $inventoryItem?->brand),
            'model' => $this->nullableInput($data, 'model', $inventoryItem?->model),
            'serial_number' => $this->nullableInput($data, 'serial_number', $inventoryItem?->serial_number),
            'po_number' => $this->nullableInput($data, 'po_number', $inventoryItem?->po_number),
            'drive_folder_id' => $this->nullableInput($data, 'drive_folder_id', $inventoryItem?->drive_folder_id),
            'remarks' => $this->nullableInput($data, 'remarks', $inventoryItem?->remarks),
        ];
    }

    private function assertItemIsValid(string $itemId): Item
    {
        $item = Item::query()
            ->withTrashed()
            ->find($itemId);

        if (! $item || $item->trashed() || $item->tracking_type !== 'property') {
            throw ValidationException::withMessages([
                'item_id' => ['Selected item is invalid or is not property-tracked.'],
            ]);
        }

        return $item;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertLifecycleRules(array $payload, Item $item): void
    {
        if (! in_array((string) $payload['custody_state'], InventoryCustodyStates::values(), true)) {
            throw ValidationException::withMessages([
                'custody_state' => ['Custody state must be either pool or issued.'],
            ]);
        }

        if (! in_array((string) $payload['status'], InventoryStatuses::values(), true)) {
            throw ValidationException::withMessages([
                'status' => ['Status is invalid.'],
            ]);
        }

        if (! in_array((string) $payload['condition'], InventoryConditions::values(), true)) {
            throw ValidationException::withMessages([
                'condition' => ['Condition is invalid.'],
            ]);
        }

        if ((bool) ($item->requires_serial ?? false) && trim((string) ($payload['serial_number'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'serial_number' => ['A serial number is required for the selected item.'],
            ]);
        }
    }

    private function resolveAccountableOfficerName(?string $accountableOfficerId, mixed $fallbackName): ?string
    {
        $accountableOfficerId = trim((string) ($accountableOfficerId ?? ''));

        if ($accountableOfficerId !== '') {
            $accountableOfficer = AccountableOfficer::query()
                ->withTrashed()
                ->find($accountableOfficerId);

            if ($accountableOfficer) {
                return $this->nullableString($accountableOfficer->full_name);
            }
        }

        return $this->nullableString($fallbackName);
    }

    /**
     * @return array<int, string>
     */
    private function auditFields(): array
    {
        return [
            'item_id',
            'department_id',
            'fund_source_id',
            'property_number',
            'acquisition_date',
            'acquisition_cost',
            'description',
            'quantity',
            'unit',
            'stock_number',
            'service_life',
            'is_ics',
            'accountable_officer',
            'accountable_officer_id',
            'custody_state',
            'status',
            'condition',
            'brand',
            'model',
            'serial_number',
            'po_number',
            'drive_folder_id',
            'remarks',
            'air_item_unit_id',
        ];
    }

    private function buildCreatedDisplay(InventoryItem $inventoryItem): array
    {
        return [
            'summary' => 'Inventory item created: ' . $this->inventoryItemLabel($inventoryItem),
            'subject_label' => $this->inventoryItemLabel($inventoryItem),
            'sections' => [[
                'title' => 'Inventory Item Details',
                'items' => [
                    ['label' => 'Item', 'before' => 'None', 'after' => $this->resolveItemLabel($inventoryItem->item_id)],
                    ['label' => 'Department', 'before' => 'None', 'after' => $this->resolveDepartmentLabel($inventoryItem->department_id)],
                    ['label' => 'Fund Source', 'before' => 'None', 'after' => $this->resolveFundSourceLabel($inventoryItem->fund_source_id)],
                    ['label' => 'Property Number', 'before' => 'None', 'after' => $this->displayValue($inventoryItem->property_number)],
                    ['label' => 'Classification', 'before' => 'None', 'after' => $this->classificationLabel($inventoryItem->is_ics)],
                    ['label' => 'Custody State', 'before' => 'None', 'after' => $this->custodyStateLabel($inventoryItem->custody_state)],
                    ['label' => 'Status', 'before' => 'None', 'after' => $this->statusLabel($inventoryItem->status)],
                    ['label' => 'Condition', 'before' => 'None', 'after' => $this->conditionLabel($inventoryItem->condition)],
                    ['label' => 'Acquisition Cost', 'before' => 'None', 'after' => $this->moneyLabel($inventoryItem->acquisition_cost)],
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
            'summary' => 'Inventory item updated: ' . $this->inventoryItemLabelFromValues($after),
            'subject_label' => $this->inventoryItemLabelFromValues($after),
            'sections' => [[
                'title' => 'Inventory Item Details',
                'items' => [
                    ['label' => 'Item', 'before' => $this->resolveItemLabel($before['item_id'] ?? null), 'after' => $this->resolveItemLabel($after['item_id'] ?? null)],
                    ['label' => 'Department', 'before' => $this->resolveDepartmentLabel($before['department_id'] ?? null), 'after' => $this->resolveDepartmentLabel($after['department_id'] ?? null)],
                    ['label' => 'Fund Source', 'before' => $this->resolveFundSourceLabel($before['fund_source_id'] ?? null), 'after' => $this->resolveFundSourceLabel($after['fund_source_id'] ?? null)],
                    ['label' => 'Property Number', 'before' => $this->displayValue($before['property_number'] ?? null), 'after' => $this->displayValue($after['property_number'] ?? null)],
                    ['label' => 'Classification', 'before' => $this->classificationLabel($before['is_ics'] ?? null), 'after' => $this->classificationLabel($after['is_ics'] ?? null)],
                    ['label' => 'Custody State', 'before' => $this->custodyStateLabel($before['custody_state'] ?? null), 'after' => $this->custodyStateLabel($after['custody_state'] ?? null)],
                    ['label' => 'Status', 'before' => $this->statusLabel($before['status'] ?? null), 'after' => $this->statusLabel($after['status'] ?? null)],
                    ['label' => 'Condition', 'before' => $this->conditionLabel($before['condition'] ?? null), 'after' => $this->conditionLabel($after['condition'] ?? null)],
                    ['label' => 'Acquisition Cost', 'before' => $this->moneyLabel($before['acquisition_cost'] ?? null), 'after' => $this->moneyLabel($after['acquisition_cost'] ?? null)],
                ],
            ]],
        ];
    }

    private function buildLifecycleDisplay(InventoryItem $inventoryItem, string $before, string $after): array
    {
        return [
            'summary' => 'Inventory item ' . strtolower($after === 'Archived' ? 'archived' : 'restored') . ': ' . $this->inventoryItemLabel($inventoryItem),
            'subject_label' => $this->inventoryItemLabel($inventoryItem),
            'sections' => [[
                'title' => 'Inventory Item Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => $before, 'after' => $after],
                ],
            ]],
        ];
    }

    private function inventoryItemLabel(InventoryItem $inventoryItem): string
    {
        return $this->inventoryItemLabelFromValues(array_merge(
            $inventoryItem->toArray(),
            ['item_name' => $inventoryItem->item?->item_name]
        ));
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function inventoryItemLabelFromValues(array $values): string
    {
        $itemId = trim((string) ($values['item_id'] ?? ''));
        $propertyNumber = trim((string) ($values['property_number'] ?? ''));
        $itemName = trim((string) ($values['item_name'] ?? ''));

        if ($itemName === '' && $itemId !== '') {
            $itemName = $this->resolveItemLabel($itemId);
        }

        if ($itemName !== '' && $propertyNumber !== '') {
            return "{$itemName} ({$propertyNumber})";
        }

        return $itemName !== '' ? $itemName : ($propertyNumber !== '' ? $propertyNumber : 'Inventory Item');
    }

    private function resolveItemLabel(mixed $itemId): string
    {
        $itemId = trim((string) ($itemId ?? ''));

        if ($itemId === '') {
            return 'None';
        }

        $item = Item::query()
            ->withTrashed()
            ->select(['id', 'item_name', 'item_identification'])
            ->find($itemId);

        if (! $item) {
            return 'Unknown Item';
        }

        $itemName = trim((string) $item->item_name);
        $identification = trim((string) ($item->item_identification ?? ''));

        if ($itemName !== '' && $identification !== '') {
            return "{$itemName} ({$identification})";
        }

        return $itemName !== '' ? $itemName : ($identification !== '' ? $identification : 'Item');
    }

    private function resolveDepartmentLabel(mixed $departmentId): string
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

    private function resolveFundSourceLabel(mixed $fundSourceId): string
    {
        $fundSourceId = trim((string) ($fundSourceId ?? ''));

        if ($fundSourceId === '') {
            return 'None';
        }

        $fundSource = FundSource::query()
            ->withTrashed()
            ->select(['id', 'code', 'name'])
            ->find($fundSourceId);

        if (! $fundSource) {
            return 'Unknown Fund Source';
        }

        $code = trim((string) ($fundSource->code ?? ''));
        $name = trim((string) ($fundSource->name ?? ''));

        if ($code !== '' && $name !== '') {
            return "{$code} ({$name})";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Fund Source');
    }

    private function classificationLabel(mixed $value): string
    {
        if ($value === null) {
            return 'None';
        }

        return (bool) $value ? 'ICS' : 'PPE';
    }

    private function custodyStateLabel(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return InventoryCustodyStates::labels()[$value] ?? 'None';
    }

    private function statusLabel(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return InventoryStatuses::labels()[$value] ?? 'None';
    }

    private function conditionLabel(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return InventoryConditions::labels()[$value] ?? 'None';
    }

    private function moneyLabel(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'None';
        }

        if (! is_numeric($value)) {
            return (string) $value;
        }

        return number_format((float) $value, 2);
    }

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
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
