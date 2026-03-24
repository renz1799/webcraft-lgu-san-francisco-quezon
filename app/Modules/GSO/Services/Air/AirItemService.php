<?php

namespace App\Modules\GSO\Services\Air;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Repositories\Contracts\AirItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use App\Modules\GSO\Services\Contracts\Air\AirItemServiceInterface;
use App\Modules\GSO\Support\Air\AirStatuses;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AirItemService implements AirItemServiceInterface
{
    public function __construct(
        private readonly AirRepositoryInterface $airs,
        private readonly AirItemRepositoryInterface $airItems,
        private readonly AuditLogServiceInterface $auditLogs,
    ) {}

    public function listForAir(string $airId): array
    {
        return $this->airItems->listByAirId($airId)
            ->map(fn (AirItem $airItem): array => $this->serializeAirItem($airItem))
            ->values()
            ->all();
    }

    public function suggestItems(string $airId, string $query, int $limit = 10): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            return [];
        }

        return Item::query()
            ->with([
                'unitConversions' => fn ($builder) => $builder
                    ->select(['id', 'item_id', 'from_unit', 'multiplier']),
            ])
            ->where(function ($builder) use ($query) {
                $builder->where('item_name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('item_identification', 'like', "%{$query}%")
                    ->orWhere('major_sub_account_group', 'like', "%{$query}%");
            })
            ->orderBy('item_name')
            ->limit(max(1, min($limit, 25)))
            ->get([
                'id',
                'item_name',
                'description',
                'base_unit',
                'item_identification',
                'tracking_type',
                'requires_serial',
                'is_semi_expendable',
            ])
            ->map(fn (Item $item): array => $this->serializeSuggestedItem($item))
            ->values()
            ->all();
    }

    public function addItemToDraft(string $actorUserId, string $airId, array $data): AirItem
    {
        return DB::transaction(function () use ($actorUserId, $airId, $data) {
            $air = $this->assertEditableDraft($airId);
            $item = $this->findItemWithUnitsOrFail((string) ($data['item_id'] ?? ''));
            $unitSnapshot = $item->canonicalUnitValue((string) ($data['unit_snapshot'] ?? ''));

            if ($unitSnapshot === null) {
                throw ValidationException::withMessages([
                    'unit_snapshot' => ['Select a configured unit for this item.'],
                ]);
            }

            $created = $this->airItems->create([
                'air_id' => (string) $air->id,
                'item_id' => (string) $item->id,
                'stock_no_snapshot' => $this->nullableString($item->item_identification),
                'item_name_snapshot' => $this->cleanString((string) ($item->item_name ?? '')),
                'description_snapshot' => $this->nullableString($data['description_snapshot'] ?? $item->description),
                'unit_snapshot' => $unitSnapshot,
                'acquisition_cost' => $this->normalizeDecimal($data['acquisition_cost'] ?? null),
                'qty_ordered' => max(1, (int) ($data['qty_ordered'] ?? 1)),
                'qty_delivered' => 0,
                'qty_accepted' => 0,
                'tracking_type_snapshot' => $this->nullableString($item->tracking_type) ?? 'property',
                'requires_serial_snapshot' => (bool) ($item->requires_serial ?? false),
                'is_semi_expendable_snapshot' => (bool) ($item->is_semi_expendable ?? false),
                'remarks' => null,
            ]);

            $this->auditLogs->record(
                action: 'gso.air-item.created',
                subject: $created,
                changesOld: [],
                changesNew: $this->snapshotAirItem($created),
                meta: ['actor_user_id' => $actorUserId, 'air_id' => (string) $air->id],
                message: 'GSO AIR item added: ' . $this->airItemLabel($created),
                display: $this->buildCreatedDisplay($air, $created),
            );

            return $created;
        });
    }

    public function updateItemInDraft(string $actorUserId, string $airId, string $airItemId, array $data): AirItem
    {
        return DB::transaction(function () use ($actorUserId, $airId, $airItemId, $data) {
            $air = $this->assertEditableDraft($airId);
            $airItem = $this->airItems->findOrFail($airItemId);
            $this->assertAirOwnership($airItem, (string) $air->id);

            $before = $this->snapshotAirItem($airItem);

            if (array_key_exists('description_snapshot', $data)) {
                $airItem->description_snapshot = $this->nullableString($data['description_snapshot']);
            }

            if (array_key_exists('qty_ordered', $data) && $data['qty_ordered'] !== null) {
                $airItem->qty_ordered = max(1, (int) $data['qty_ordered']);
            }

            if (array_key_exists('unit_snapshot', $data)) {
                $item = $airItem->relationLoaded('item')
                    ? $airItem->item
                    : $this->findItemWithUnitsOrFail((string) $airItem->item_id);

                $airItem->unit_snapshot = $item?->canonicalUnitValue((string) ($data['unit_snapshot'] ?? ''));
            }

            if (array_key_exists('acquisition_cost', $data)) {
                $airItem->acquisition_cost = $this->normalizeDecimal($data['acquisition_cost']);
            }

            if (array_key_exists('remarks', $data)) {
                $airItem->remarks = $this->nullableString($data['remarks']);
            }

            if ($airItem->unit_snapshot === null) {
                throw ValidationException::withMessages([
                    'unit_snapshot' => ['Select a configured unit for this item.'],
                ]);
            }

            $updated = $this->airItems->save($airItem);
            $after = $this->snapshotAirItem($updated);

            $this->auditLogs->record(
                action: 'gso.air-item.updated',
                subject: $updated,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId, 'air_id' => (string) $air->id],
                message: 'GSO AIR item updated: ' . $this->airItemLabel($updated),
                display: $this->buildUpdatedDisplay($updated, $before, $after),
            );

            return $updated;
        });
    }

    public function bulkUpdateItemsInDraft(string $actorUserId, string $airId, array $items): void
    {
        DB::transaction(function () use ($actorUserId, $airId, $items) {
            $this->assertEditableDraft($airId);

            foreach ($items as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $this->updateItemInDraft(
                    actorUserId: $actorUserId,
                    airId: $airId,
                    airItemId: (string) ($row['id'] ?? ''),
                    data: $row,
                );
            }
        });
    }

    public function removeItemFromDraft(string $actorUserId, string $airId, string $airItemId): void
    {
        DB::transaction(function () use ($actorUserId, $airId, $airItemId) {
            $air = $this->assertEditableDraft($airId);
            $airItem = $this->airItems->findOrFail($airItemId);
            $this->assertAirOwnership($airItem, (string) $air->id);
            $before = $this->snapshotAirItem($airItem);

            $this->airItems->delete($airItem);

            $this->auditLogs->record(
                action: 'gso.air-item.deleted',
                subject: $airItem,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId, 'air_id' => (string) $air->id],
                message: 'GSO AIR item removed: ' . $this->airItemLabelFromValues($before),
                display: $this->buildDeletedDisplay($air, $before),
            );
        });
    }

    private function assertEditableDraft(string $airId): Air
    {
        $air = $this->airs->findOrFail($airId, true);

        if ($air->trashed()) {
            throw ValidationException::withMessages([
                'air' => ['Restore this AIR before editing its items.'],
            ]);
        }

        if ((string) ($air->status ?? '') !== AirStatuses::DRAFT) {
            throw ValidationException::withMessages([
                'status' => ['Only draft AIR records can edit item rows in this migration slice.'],
            ]);
        }

        return $air;
    }

    private function assertAirOwnership(AirItem $airItem, string $airId): void
    {
        if ((string) $airItem->air_id !== $airId) {
            throw ValidationException::withMessages([
                'air_item' => ['This item row does not belong to the selected AIR.'],
            ]);
        }
    }

    private function findItemWithUnitsOrFail(string $itemId): Item
    {
        return Item::query()
            ->with([
                'unitConversions' => fn ($query) => $query
                    ->select(['id', 'item_id', 'from_unit', 'multiplier']),
            ])
            ->findOrFail($itemId);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAirItem(AirItem $airItem): array
    {
        $item = $airItem->relationLoaded('item') ? $airItem->item : null;
        $trackingType = $this->nullableString($airItem->tracking_type_snapshot) ?? 'property';

        return [
            'id' => (string) $airItem->id,
            'air_id' => (string) $airItem->air_id,
            'item_id' => (string) $airItem->item_id,
            'stock_no_snapshot' => $this->nullableString($airItem->stock_no_snapshot),
            'item_name_snapshot' => $this->nullableString($airItem->item_name_snapshot),
            'description_snapshot' => $this->nullableString($airItem->description_snapshot),
            'unit_snapshot' => $this->nullableString($airItem->unit_snapshot),
            'acquisition_cost' => $airItem->acquisition_cost !== null ? (string) $airItem->acquisition_cost : null,
            'acquisition_cost_text' => $airItem->acquisition_cost !== null ? number_format((float) $airItem->acquisition_cost, 2) : '-',
            'qty_ordered' => (int) ($airItem->qty_ordered ?? 0),
            'qty_delivered' => (int) ($airItem->qty_delivered ?? 0),
            'qty_accepted' => (int) ($airItem->qty_accepted ?? 0),
            'tracking_type_snapshot' => $trackingType,
            'tracking_type_text' => $trackingType === 'consumable' ? 'Consumable' : 'Property',
            'requires_serial_snapshot' => (bool) ($airItem->requires_serial_snapshot ?? false),
            'is_semi_expendable_snapshot' => (bool) ($airItem->is_semi_expendable_snapshot ?? false),
            'remarks' => $this->nullableString($airItem->remarks),
            'item_label' => $this->airItemLabel($airItem),
            'available_units' => $item?->getAvailableUnitOptions() ?? [],
            'linked_item_missing' => $item === null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSuggestedItem(Item $item): array
    {
        $trackingType = $this->nullableString($item->tracking_type) ?? 'property';

        return [
            'id' => (string) $item->id,
            'item_name' => $this->cleanString((string) ($item->item_name ?? '')),
            'description' => $this->nullableString($item->description),
            'base_unit' => $this->nullableString($item->base_unit),
            'stock_no' => $this->nullableString($item->item_identification),
            'tracking_type' => $trackingType,
            'tracking_type_text' => $trackingType === 'consumable' ? 'Consumable' : 'Property',
            'requires_serial' => (bool) ($item->requires_serial ?? false),
            'is_semi_expendable' => (bool) ($item->is_semi_expendable ?? false),
            'available_units' => $item->getAvailableUnitOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotAirItem(AirItem $airItem): array
    {
        return [
            'item_name_snapshot' => $this->nullableString($airItem->item_name_snapshot),
            'stock_no_snapshot' => $this->nullableString($airItem->stock_no_snapshot),
            'description_snapshot' => $this->nullableString($airItem->description_snapshot),
            'unit_snapshot' => $this->nullableString($airItem->unit_snapshot),
            'acquisition_cost' => $airItem->acquisition_cost !== null ? (string) $airItem->acquisition_cost : null,
            'qty_ordered' => (int) ($airItem->qty_ordered ?? 0),
            'qty_delivered' => (int) ($airItem->qty_delivered ?? 0),
            'qty_accepted' => (int) ($airItem->qty_accepted ?? 0),
            'tracking_type_snapshot' => $this->nullableString($airItem->tracking_type_snapshot),
            'requires_serial_snapshot' => (bool) ($airItem->requires_serial_snapshot ?? false),
            'is_semi_expendable_snapshot' => (bool) ($airItem->is_semi_expendable_snapshot ?? false),
            'remarks' => $this->nullableString($airItem->remarks),
        ];
    }

    private function buildCreatedDisplay(Air $air, AirItem $airItem): array
    {
        return [
            'summary' => 'AIR item added: ' . $this->airItemLabel($airItem),
            'subject_label' => $this->airItemLabel($airItem),
            'sections' => [[
                'title' => 'AIR Item Details',
                'items' => [
                    ['label' => 'AIR', 'before' => 'None', 'after' => $this->displayValue($air->po_number)],
                    ['label' => 'Quantity Ordered', 'before' => 'None', 'after' => (string) ((int) ($airItem->qty_ordered ?? 0))],
                    ['label' => 'Unit', 'before' => 'None', 'after' => $this->displayValue($airItem->unit_snapshot)],
                    ['label' => 'Acquisition Cost', 'before' => 'None', 'after' => $this->displayMoney($airItem->acquisition_cost)],
                ],
            ]],
        ];
    }

    private function buildUpdatedDisplay(AirItem $airItem, array $before, array $after): array
    {
        return [
            'summary' => 'AIR item updated: ' . $this->airItemLabel($airItem),
            'subject_label' => $this->airItemLabel($airItem),
            'sections' => [[
                'title' => 'AIR Item Details',
                'items' => [
                    ['label' => 'Description', 'before' => $this->displayValue($before['description_snapshot'] ?? null), 'after' => $this->displayValue($after['description_snapshot'] ?? null)],
                    ['label' => 'Quantity Ordered', 'before' => (string) ($before['qty_ordered'] ?? 0), 'after' => (string) ($after['qty_ordered'] ?? 0)],
                    ['label' => 'Unit', 'before' => $this->displayValue($before['unit_snapshot'] ?? null), 'after' => $this->displayValue($after['unit_snapshot'] ?? null)],
                    ['label' => 'Acquisition Cost', 'before' => $this->displayMoney($before['acquisition_cost'] ?? null), 'after' => $this->displayMoney($after['acquisition_cost'] ?? null)],
                ],
            ]],
        ];
    }

    private function buildDeletedDisplay(Air $air, array $before): array
    {
        return [
            'summary' => 'AIR item removed: ' . $this->airItemLabelFromValues($before),
            'subject_label' => $this->airItemLabelFromValues($before),
            'sections' => [[
                'title' => 'AIR Item Details',
                'items' => [
                    ['label' => 'AIR', 'before' => $this->displayValue($air->po_number), 'after' => $this->displayValue($air->po_number)],
                    ['label' => 'Quantity Ordered', 'before' => (string) ($before['qty_ordered'] ?? 0), 'after' => 'Removed'],
                    ['label' => 'Unit', 'before' => $this->displayValue($before['unit_snapshot'] ?? null), 'after' => 'Removed'],
                ],
            ]],
        ];
    }

    private function airItemLabel(AirItem $airItem): string
    {
        return $this->airItemLabelFromValues([
            'item_name_snapshot' => $airItem->item_name_snapshot,
            'stock_no_snapshot' => $airItem->stock_no_snapshot,
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function airItemLabelFromValues(array $values): string
    {
        $itemName = $this->cleanString((string) ($values['item_name_snapshot'] ?? ''));
        $stockNo = $this->cleanString((string) ($values['stock_no_snapshot'] ?? ''));

        if ($itemName !== '' && $stockNo !== '') {
            return "{$itemName} ({$stockNo})";
        }

        return $itemName !== '' ? $itemName : ($stockNo !== '' ? $stockNo : 'AIR Item');
    }

    private function cleanString(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim($value)) ?? '';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = $this->cleanString((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    private function normalizeDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }

    private function displayMoney(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'None';
        }

        return number_format((float) $value, 2);
    }
}
