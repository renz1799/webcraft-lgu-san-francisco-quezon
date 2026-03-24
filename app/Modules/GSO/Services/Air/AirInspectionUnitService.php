<?php

namespace App\Modules\GSO\Services\Air;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\AirItemUnit;
use App\Modules\GSO\Repositories\Contracts\AirItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirItemUnitFileRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirItemUnitRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Services\AssetComponentService;
use App\Modules\GSO\Services\Contracts\Air\AirInspectionUnitServiceInterface;
use App\Modules\GSO\Support\Air\AirStatuses;
use App\Modules\GSO\Support\InventoryConditions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AirInspectionUnitService implements AirInspectionUnitServiceInterface
{
    public function __construct(
        private readonly AirRepositoryInterface $airs,
        private readonly AirItemRepositoryInterface $airItems,
        private readonly AirItemUnitRepositoryInterface $units,
        private readonly AirItemUnitFileRepositoryInterface $files,
        private readonly InventoryItemRepositoryInterface $inventoryItems,
        private readonly AssetComponentService $components,
        private readonly AuditLogServiceInterface $auditLogs,
    ) {}

    public function listForAirItem(string $airId, string $airItemId): array
    {
        [$air, $airItem] = $this->resolveLineage($airId, $airItemId, false);

        return $this->buildPayload($air, $airItem);
    }

    public function saveForAirItem(string $actorUserId, string $airId, string $airItemId, array $units): array
    {
        [$air, $airItem] = $this->resolveLineage($airId, $airItemId, true);

        if (! $this->requiresUnitTracking($airItem) && $this->filterMeaningfulRows($units) !== []) {
            throw ValidationException::withMessages([
                'units' => ['This AIR item does not require inspection unit rows.'],
            ]);
        }

        $acceptedQty = max(0, (int) ($airItem->qty_accepted ?? 0));
        $rows = $this->filterMeaningfulRows($units);

        if ($acceptedQty <= 0 && $rows !== []) {
            throw ValidationException::withMessages([
                'qty_accepted' => ['Save an accepted quantity before encoding unit rows.'],
            ]);
        }

        if (count($rows) > $acceptedQty) {
            throw ValidationException::withMessages([
                'units' => ['Unit rows cannot exceed the accepted quantity for this AIR item.'],
            ]);
        }

        DB::transaction(function () use ($actorUserId, $airItem, $rows): void {
            $savedCount = 0;

            foreach ($rows as $index => $row) {
                $unit = $this->upsertUnit($airItem, $row);
                $normalizedComponents = $this->resolveComponentRowsForSave(
                    airItem: $airItem,
                    unit: $unit,
                    row: $row,
                    field: "units.{$index}.components",
                    contextLabel: 'Row ' . ($index + 1) . ' components',
                );
                $this->components->syncAirUnitComponents($unit, $normalizedComponents);
                $unit->load('components');
                $savedCount++;

                $this->auditLogs->record(
                    action: isset($row['id']) && trim((string) $row['id']) !== ''
                        ? 'gso.air.inspection.unit.updated'
                        : 'gso.air.inspection.unit.created',
                    subject: $unit,
                    changesOld: [],
                    changesNew: $this->snapshotUnit($unit),
                    meta: ['actor_user_id' => $actorUserId, 'air_item_id' => (string) $airItem->id],
                    message: 'AIR inspection unit saved: ' . $this->unitLabel($unit),
                    display: [
                        'summary' => 'AIR inspection unit saved: ' . $this->unitLabel($unit),
                        'subject_label' => $this->unitLabel($unit),
                        'sections' => [[
                            'title' => 'Unit Details',
                            'items' => [
                                ['label' => 'Brand', 'before' => 'None', 'after' => $this->displayValue($unit->brand)],
                                ['label' => 'Model', 'before' => 'None', 'after' => $this->displayValue($unit->model)],
                                ['label' => 'Serial Number', 'before' => 'None', 'after' => $this->displayValue($unit->serial_number)],
                                ['label' => 'Condition', 'before' => 'None', 'after' => InventoryConditions::labels()[(string) ($unit->condition_status ?? '')] ?? $this->displayValue($unit->condition_status)],
                                ['label' => 'Components', 'before' => '0', 'after' => (string) count($this->serializeComponentsForUnit($unit))],
                            ],
                        ]],
                    ],
                );
            }

            if ($savedCount === 0) {
                $this->auditLogs->record(
                    action: 'gso.air.inspection.unit.noop',
                    subject: $airItem,
                    changesOld: [],
                    changesNew: ['saved_count' => 0],
                    meta: ['actor_user_id' => $actorUserId],
                    message: 'AIR inspection units checked with no saved rows: ' . $this->airItemLabel($airItem),
                    display: [
                        'summary' => 'No inspection unit rows were saved',
                        'subject_label' => $this->airItemLabel($airItem),
                    ],
                );
            }
        });

        return $this->buildPayload($air, $airItem);
    }

    public function deleteUnit(string $actorUserId, string $airId, string $airItemId, string $unitId): array
    {
        [$air, $airItem] = $this->resolveLineage($airId, $airItemId, true);
        $unit = $this->units->findForAirItemOrFail((string) $airItem->id, $unitId);

        if ($this->files->hasActiveFiles((string) $unit->id)) {
            throw ValidationException::withMessages([
                'unit' => ['Delete the unit files first before removing this unit row.'],
            ]);
        }

        if (trim((string) ($unit->inventory_item_id ?? '')) !== '') {
            throw ValidationException::withMessages([
                'unit' => ['This unit is already linked to an inventory record and cannot be removed here.'],
            ]);
        }

        $before = $this->snapshotUnit($unit);
        $this->components->syncAirUnitComponents($unit, []);
        $this->units->delete($unit);

        $this->auditLogs->record(
            action: 'gso.air.inspection.unit.deleted',
            subject: $airItem,
            changesOld: $before,
            changesNew: ['deleted_at' => now()->toDateTimeString()],
            meta: ['actor_user_id' => $actorUserId, 'air_item_id' => (string) $airItem->id],
            message: 'AIR inspection unit removed: ' . $this->unitLabelFromValues($before),
            display: [
                'summary' => 'AIR inspection unit removed: ' . $this->unitLabelFromValues($before),
                'subject_label' => $this->airItemLabel($airItem),
            ],
        );

        return $this->buildPayload($air, $airItem);
    }

    /**
     * @return array{0: Air, 1: AirItem}
     */
    private function resolveLineage(string $airId, string $airItemId, bool $requireEditable): array
    {
        $air = $this->airs->findOrFail($airId, true);

        if ($air->trashed()) {
            throw ValidationException::withMessages([
                'air' => ['Archived AIR records cannot manage inspection units.'],
            ]);
        }

        $allowedStatuses = $requireEditable
            ? [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS]
            : [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS, AirStatuses::INSPECTED];

        if (! in_array((string) ($air->status ?? ''), $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => ['This AIR is not in a state that supports inspection unit work.'],
            ]);
        }

        $airItem = $this->airItems->findOrFail($airItemId);

        if ((string) $airItem->air_id !== (string) $air->id) {
            throw ValidationException::withMessages([
                'air_item' => ['The selected AIR item does not belong to this AIR record.'],
            ]);
        }

        return [$air, $airItem];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function filterMeaningfulRows(array $rows): array
    {
        return collect($rows)
            ->filter(fn (mixed $row): bool => is_array($row) && $this->rowHasMeaningfulContent($row))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function upsertUnit(AirItem $airItem, array $row): AirItemUnit
    {
        $condition = trim((string) ($row['condition_status'] ?? ''));

        if ($condition === '') {
            throw ValidationException::withMessages([
                'units' => ['Each inspection unit row must include a condition status.'],
            ]);
        }

        if (! in_array($condition, InventoryConditions::values(), true)) {
            throw ValidationException::withMessages([
                'units' => ['Inspection unit condition status is invalid.'],
            ]);
        }

        if ((bool) ($airItem->requires_serial_snapshot ?? false) && trim((string) ($row['serial_number'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'units' => ['Serial number is required for this AIR item.'],
            ]);
        }

        $inventoryItemId = $this->nullableString($row['inventory_item_id'] ?? null);
        if ($inventoryItemId !== null) {
            $this->inventoryItems->findOrFail($inventoryItemId, true);
        }

        $unitId = trim((string) ($row['id'] ?? ''));
        $unit = $unitId !== ''
            ? $this->units->findForAirItemOrFail((string) $airItem->id, $unitId)
            : new AirItemUnit(['air_item_id' => (string) $airItem->id]);

        $unit->air_item_id = (string) $airItem->id;
        $unit->inventory_item_id = $inventoryItemId;
        $unit->brand = $this->nullableString($row['brand'] ?? null);
        $unit->model = $this->nullableString($row['model'] ?? null);
        $unit->serial_number = $this->nullableString($row['serial_number'] ?? null);
        $unit->property_number = $this->nullableString($row['property_number'] ?? null);
        $unit->condition_status = $condition;
        $unit->condition_notes = $this->nullableString($row['condition_notes'] ?? null);

        return $unit->exists
            ? $this->units->save($unit)
            : $this->units->create($unit->getAttributes());
    }

    /**
     * @return array{air: array<string, mixed>, air_item: array<string, mixed>, units: array<int, array<string, mixed>>}
     */
    private function buildPayload(Air $air, AirItem $airItem): array
    {
        $defaultComponents = $airItem->item instanceof \App\Modules\GSO\Models\Item
            ? $this->components->makeDefaultComponentsFromTemplates($airItem->item)
            : [];
        $units = $this->units->listForAirItem((string) $airItem->id)
            ->map(fn (AirItemUnit $unit): array => $this->serializeUnit($unit))
            ->values()
            ->all();

        return [
            'air' => [
                'id' => (string) $air->id,
                'label' => $this->airLabel($air),
                'status' => (string) ($air->status ?? ''),
                'status_text' => AirStatuses::label((string) ($air->status ?? '')),
                'po_number' => $this->nullableString($air->po_number),
                'can_edit' => in_array((string) ($air->status ?? ''), [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS], true),
            ],
            'air_item' => [
                'id' => (string) $airItem->id,
                'label' => $this->airItemLabel($airItem),
                'unit_snapshot' => $this->nullableString($airItem->unit_snapshot),
                'qty_ordered' => (int) ($airItem->qty_ordered ?? 0),
                'qty_delivered' => (int) ($airItem->qty_delivered ?? 0),
                'qty_accepted' => (int) ($airItem->qty_accepted ?? 0),
                'units_count' => count($units),
                'remaining_unit_slots' => max(0, (int) ($airItem->qty_accepted ?? 0) - count($units)),
                'needs_units' => $this->requiresUnitTracking($airItem),
                'requires_serial' => (bool) ($airItem->requires_serial_snapshot ?? false),
                'condition_statuses' => InventoryConditions::labels(),
                'default_components' => $defaultComponents,
            ],
            'units' => $units,
        ];
    }

    private function requiresUnitTracking(AirItem $airItem): bool
    {
        $trackingType = strtolower(trim((string) ($airItem->tracking_type_snapshot ?? '')));

        return $trackingType === 'property'
            || (bool) ($airItem->requires_serial_snapshot ?? false)
            || (bool) ($airItem->is_semi_expendable_snapshot ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeUnit(AirItemUnit $unit): array
    {
        $components = $this->serializeComponentsForUnit($unit);
        $summary = $this->components->summarize($components);

        return [
            'id' => (string) $unit->id,
            'air_item_id' => (string) $unit->air_item_id,
            'inventory_item_id' => $this->nullableString($unit->inventory_item_id),
            'brand' => $this->nullableString($unit->brand),
            'model' => $this->nullableString($unit->model),
            'serial_number' => $this->nullableString($unit->serial_number),
            'property_number' => $this->nullableString($unit->property_number),
            'condition_status' => $this->nullableString($unit->condition_status),
            'condition_status_text' => InventoryConditions::labels()[(string) ($unit->condition_status ?? '')] ?? 'Unknown',
            'condition_notes' => $this->nullableString($unit->condition_notes),
            'drive_folder_id' => $this->nullableString($unit->drive_folder_id),
            'file_count' => (int) ($unit->files_count ?? 0),
            'component_count' => count($components),
            'components' => $components,
            'has_components' => (bool) ($summary['has_components'] ?? false),
            'component_total_cost' => (float) ($summary['component_total_cost'] ?? 0),
            'components_complete' => (bool) ($summary['components_complete'] ?? false),
            'component_cost_warning' => $this->components->getComponentCostWarning(
                rows: $components,
                parentUnitCost: $unit->airItem?->acquisition_cost,
                contextLabel: 'Component schedule',
            ),
            'label' => $this->unitLabel($unit),
            'created_at' => $unit->created_at?->toDateTimeString(),
            'created_at_text' => $unit->created_at?->format('M d, Y h:i A') ?? '-',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotUnit(AirItemUnit $unit): array
    {
        return [
            'brand' => $this->nullableString($unit->brand),
            'model' => $this->nullableString($unit->model),
            'serial_number' => $this->nullableString($unit->serial_number),
            'property_number' => $this->nullableString($unit->property_number),
            'condition_status' => $this->nullableString($unit->condition_status),
            'condition_notes' => $this->nullableString($unit->condition_notes),
            'inventory_item_id' => $this->nullableString($unit->inventory_item_id),
            'components' => $this->serializeComponentsForUnit($unit),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowHasMeaningfulContent(array $row): bool
    {
        foreach (['id', 'brand', 'model', 'serial_number', 'property_number', 'condition_status', 'condition_notes', 'inventory_item_id'] as $field) {
            if (trim((string) ($row[$field] ?? '')) !== '') {
                return true;
            }
        }

        return $this->components->normalizeComponentRows(
            is_array($row['components'] ?? null) ? $row['components'] : []
        ) !== [];
    }

    private function resolveComponentRowsForSave(
        AirItem $airItem,
        AirItemUnit $unit,
        array $row,
        string $field,
        string $contextLabel,
    ): array {
        if (! array_key_exists('components', $row)) {
            return $this->serializeComponentsForUnit($unit);
        }

        return $this->components->assertComponentRowsValid(
            rows: is_array($row['components'] ?? null) ? $row['components'] : [],
            parentUnitCost: $airItem->acquisition_cost,
            field: $field,
            contextLabel: $contextLabel,
            enforceTotalMatch: false,
            requirePositiveCost: false,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function serializeComponentsForUnit(AirItemUnit $unit): array
    {
        $components = $unit->relationLoaded('components')
            ? $unit->components
            : $unit->components()->get();

        return $this->components->serializeComponents($components);
    }

    private function airLabel(Air $air): string
    {
        $poNumber = trim((string) ($air->po_number ?? ''));
        $airNumber = trim((string) ($air->air_number ?? ''));

        if ($poNumber !== '' && $airNumber !== '') {
            return "{$poNumber} / {$airNumber}";
        }

        return $poNumber !== '' ? $poNumber : ($airNumber !== '' ? $airNumber : 'AIR Record');
    }

    private function airItemLabel(AirItem $airItem): string
    {
        $itemName = trim((string) ($airItem->item_name_snapshot ?? ''));
        $stockNo = trim((string) ($airItem->stock_no_snapshot ?? ''));

        if ($itemName !== '' && $stockNo !== '') {
            return "{$itemName} ({$stockNo})";
        }

        return $itemName !== '' ? $itemName : ($stockNo !== '' ? $stockNo : 'AIR Item');
    }

    private function unitLabel(AirItemUnit $unit): string
    {
        return $this->unitLabelFromValues([
            'serial_number' => $unit->serial_number,
            'property_number' => $unit->property_number,
            'brand' => $unit->brand,
            'model' => $unit->model,
        ]);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function unitLabelFromValues(array $values): string
    {
        $serial = trim((string) ($values['serial_number'] ?? ''));
        $property = trim((string) ($values['property_number'] ?? ''));
        $brand = trim((string) ($values['brand'] ?? ''));
        $model = trim((string) ($values['model'] ?? ''));

        if ($property !== '' && $serial !== '') {
            return "{$property} / {$serial}";
        }

        if ($serial !== '') {
            return $serial;
        }

        if ($property !== '') {
            return $property;
        }

        if ($brand !== '' && $model !== '') {
            return "{$brand} {$model}";
        }

        if ($brand !== '') {
            return $brand;
        }

        if ($model !== '') {
            return $model;
        }

        return 'Inspection Unit';
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
}
