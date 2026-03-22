<?php

namespace App\Modules\GSO\Services;

use App\Modules\GSO\Models\AirItemUnit;
use App\Modules\GSO\Models\AirItemUnitComponent;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\InventoryItemComponent;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Models\ItemComponentTemplate;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class AssetComponentService
{
    public function serializeTemplates(iterable $rows): array
    {
        return collect($rows)
            ->sortBy(fn ($row) => [(int) data_get($row, 'line_no', 0), (string) data_get($row, 'name', '')])
            ->values()
            ->map(function ($row, int $index): array {
                return [
                    'id' => $this->nullableTrim(data_get($row, 'id')),
                    'line_no' => (int) data_get($row, 'line_no', $index + 1),
                    'name' => trim((string) data_get($row, 'name', '')),
                    'quantity' => max(1, (int) data_get($row, 'quantity', 1)),
                    'unit' => $this->nullableTrim(data_get($row, 'unit')),
                    'component_cost' => $this->moneyAsFloat(data_get($row, 'component_cost', 0)),
                    'remarks' => $this->nullableTrim(data_get($row, 'remarks')),
                ];
            })
            ->all();
    }

    public function serializeComponents(iterable $rows): array
    {
        return collect($rows)
            ->sortBy(fn ($row) => [(int) data_get($row, 'line_no', 0), (string) data_get($row, 'name', '')])
            ->values()
            ->map(function ($row, int $index): array {
                return [
                    'id' => $this->nullableTrim(data_get($row, 'id')),
                    'line_no' => (int) data_get($row, 'line_no', $index + 1),
                    'name' => trim((string) data_get($row, 'name', '')),
                    'quantity' => max(1, (int) data_get($row, 'quantity', 1)),
                    'unit' => $this->nullableTrim(data_get($row, 'unit')),
                    'component_cost' => $this->moneyAsFloat(data_get($row, 'component_cost', 0)),
                    'serial_number' => $this->nullableTrim(data_get($row, 'serial_number')),
                    'condition' => $this->nullableTrim(data_get($row, 'condition')),
                    'is_present' => (bool) data_get($row, 'is_present', true),
                    'remarks' => $this->nullableTrim(data_get($row, 'remarks')),
                ];
            })
            ->all();
    }

    public function summarize(iterable $rows): array
    {
        $serialized = $this->serializeComponents($rows);
        $totalCents = 0;
        $allPresent = true;

        foreach ($serialized as $row) {
            $totalCents += max(1, (int) ($row['quantity'] ?? 1)) * $this->moneyToCents($row['component_cost'] ?? 0);
            $allPresent = $allPresent && (bool) ($row['is_present'] ?? true);
        }

        return [
            'has_components' => $serialized !== [],
            'component_total_cost' => round($totalCents / 100, 2),
            'components_complete' => $serialized !== [] && $allPresent,
        ];
    }

    public function makeDefaultComponentsFromTemplates(Item $item): array
    {
        $templates = $item->relationLoaded('componentTemplates')
            ? $item->componentTemplates
            : $item->componentTemplates()->get();

        return array_map(function (array $row): array {
            return [
                'id' => null,
                'line_no' => (int) ($row['line_no'] ?? 0),
                'name' => (string) ($row['name'] ?? ''),
                'quantity' => max(1, (int) ($row['quantity'] ?? 1)),
                'unit' => $row['unit'] ?? null,
                'component_cost' => (float) ($row['component_cost'] ?? 0),
                'serial_number' => null,
                'condition' => null,
                'is_present' => true,
                'remarks' => $row['remarks'] ?? null,
            ];
        }, $this->serializeTemplates($templates));
    }

    public function syncItemTemplates(Item $item, array $rows): array
    {
        ItemComponentTemplate::query()
            ->where('item_id', (string) $item->id)
            ->delete();

        $normalized = $item->tracking_type === 'property'
            ? $this->normalizeTemplateRows($rows)
            : [];

        foreach ($normalized as $row) {
            ItemComponentTemplate::query()->create(array_merge($row, [
                'item_id' => (string) $item->id,
            ]));
        }

        return $normalized;
    }

    public function syncAirUnitComponents(AirItemUnit $unit, array $rows): array
    {
        AirItemUnitComponent::query()
            ->where('air_item_unit_id', (string) $unit->id)
            ->delete();

        $normalized = $this->normalizeComponentRows($rows);

        foreach ($normalized as $row) {
            AirItemUnitComponent::query()->create(array_merge($row, [
                'air_item_unit_id' => (string) $unit->id,
            ]));
        }

        return $normalized;
    }

    public function syncInventoryComponents(InventoryItem $inventoryItem, array $rows): array
    {
        InventoryItemComponent::query()
            ->where('inventory_item_id', (string) $inventoryItem->id)
            ->delete();

        $normalized = $this->normalizeComponentRows($rows);

        foreach ($normalized as $row) {
            InventoryItemComponent::query()->create(array_merge($row, [
                'inventory_item_id' => (string) $inventoryItem->id,
            ]));
        }

        return $normalized;
    }

    public function copyAirUnitComponentsToInventory(AirItemUnit $unit, InventoryItem $inventoryItem): array
    {
        $source = $unit->relationLoaded('components')
            ? $unit->components
            : $unit->components()->get();

        return $this->syncInventoryComponents($inventoryItem, $this->serializeComponents($source));
    }

    public function assertTemplateRowsAllowed(string $trackingType, array $rows): void
    {
        $normalized = $this->normalizeTemplateRows($rows);

        if ($trackingType !== 'property' && $normalized !== []) {
            throw ValidationException::withMessages([
                'component_templates' => ['Component templates are only allowed for property-tracked items.'],
            ]);
        }
    }

    public function assertComponentRowsValid(
        array $rows,
        mixed $parentUnitCost = null,
        string $field = 'components',
        ?string $contextLabel = null,
        bool $enforceTotalMatch = true,
        bool $requirePositiveCost = true,
    ): array {
        $normalized = $this->normalizeComponentRows($rows);

        if ($normalized === []) {
            return [];
        }

        $errors = [];
        $label = $contextLabel ?: 'Component schedule';
        $parentCostCents = $this->moneyToCents($parentUnitCost);

        if ($enforceTotalMatch && $parentCostCents <= 0) {
            $errors[$field][] = "{$label}: acquisition cost must be greater than zero when components are present.";
        }

        $totalCents = 0;

        foreach ($normalized as $index => $row) {
            $rowNo = $index + 1;
            if ($row['name'] === null) {
                $errors["{$field}.{$index}.name"][] = "{$label} row {$rowNo}: component name is required.";
            }

            if (($row['quantity'] ?? 0) < 1) {
                $errors["{$field}.{$index}.quantity"][] = "{$label} row {$rowNo}: quantity must be at least 1.";
            }

            $componentCostCents = $this->moneyToCents($row['component_cost'] ?? 0);
            if ($requirePositiveCost && $componentCostCents <= 0) {
                $errors["{$field}.{$index}.component_cost"][] = "{$label} row {$rowNo}: component cost must be greater than zero.";
            }

            $totalCents += max(1, (int) ($row['quantity'] ?? 1)) * $componentCostCents;
        }

        if ($enforceTotalMatch && $parentCostCents > 0 && $totalCents !== $parentCostCents) {
            $errors[$field][] = sprintf(
                '%s: component total (%.2f) must equal the parent unit acquisition cost (%.2f).',
                $label,
                $totalCents / 100,
                $parentCostCents / 100,
            );
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $normalized;
    }

    public function getComponentCostWarning(iterable $rows, mixed $parentUnitCost, ?string $contextLabel = null): ?string
    {
        $serialized = $this->serializeComponents($rows);
        if ($serialized === []) {
            return null;
        }

        $parentCostCents = $this->moneyToCents($parentUnitCost);
        if ($parentCostCents <= 0) {
            return null;
        }

        $totalCents = 0;
        foreach ($serialized as $row) {
            $totalCents += max(1, (int) ($row['quantity'] ?? 1)) * $this->moneyToCents($row['component_cost'] ?? 0);
        }

        if ($totalCents === $parentCostCents) {
            return null;
        }

        $label = $contextLabel ?: 'Component schedule';

        return sprintf(
            '%s total (%.2f) does not match the unit acquisition cost (%.2f). This is allowed and may reflect bundled service or labor charges.',
            $label,
            $totalCents / 100,
            $parentCostCents / 100,
        );
    }

    public function getPromotionBlockReason(AirItemUnit $unit, Item $item, mixed $parentUnitCost): ?string
    {
        $components = $unit->relationLoaded('components')
            ? $unit->components
            : $unit->components()->get();

        if ($components->isEmpty()) {
            return null;
        }

        try {
            $normalized = $this->assertComponentRowsValid(
                $this->serializeComponents($components),
                $parentUnitCost,
                'components',
                'Component schedule',
                enforceTotalMatch: false,
                requirePositiveCost: false,
            );
        } catch (ValidationException $exception) {
            return Arr::first(Arr::flatten($exception->errors())) ?: 'Component schedule is invalid.';
        }

        foreach ($normalized as $row) {
            if (! ($row['is_present'] ?? true)) {
                return 'All recorded components must be marked present before promotion.';
            }
        }

        return null;
    }

    public function hasTemplateRows(Item $item): bool
    {
        if ($item->relationLoaded('componentTemplates')) {
            return $item->componentTemplates->isNotEmpty();
        }

        return $item->componentTemplates()->exists();
    }

    public function normalizeTemplateRows(array $rows): array
    {
        $normalized = [];

        foreach (array_values($rows) as $row) {
            if (! is_array($row) || $this->isBlankTemplateRow($row)) {
                continue;
            }

            $normalized[] = [
                'line_no' => count($normalized) + 1,
                'name' => $this->nullableTrim($row['name'] ?? null),
                'quantity' => max(1, (int) ($row['quantity'] ?? 1)),
                'unit' => $this->nullableTrim($row['unit'] ?? null),
                'component_cost' => $this->centsToDecimalString($this->moneyToCents($row['component_cost'] ?? 0)),
                'remarks' => $this->nullableTrim($row['remarks'] ?? null),
            ];
        }

        return $normalized;
    }

    public function normalizeComponentRows(array $rows): array
    {
        $normalized = [];

        foreach (array_values($rows) as $row) {
            if (! is_array($row) || $this->isBlankComponentRow($row)) {
                continue;
            }

            $normalized[] = [
                'line_no' => count($normalized) + 1,
                'name' => $this->nullableTrim($row['name'] ?? null),
                'quantity' => max(1, (int) ($row['quantity'] ?? 1)),
                'unit' => $this->nullableTrim($row['unit'] ?? null),
                'component_cost' => $this->centsToDecimalString($this->moneyToCents($row['component_cost'] ?? 0)),
                'serial_number' => $this->nullableTrim($row['serial_number'] ?? null),
                'condition' => $this->nullableTrim($row['condition'] ?? null),
                'is_present' => ! array_key_exists('is_present', $row)
                    ? true
                    : filter_var($row['is_present'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
                'remarks' => $this->nullableTrim($row['remarks'] ?? null),
            ];
        }

        return $normalized;
    }

    public function isBlankTemplateRow(array $row): bool
    {
        return $this->nullableTrim($row['name'] ?? null) === null
            && $this->nullableTrim($row['unit'] ?? null) === null
            && $this->nullableTrim($row['component_cost'] ?? null) === null
            && $this->nullableTrim($row['remarks'] ?? null) === null;
    }

    public function isBlankComponentRow(array $row): bool
    {
        return $this->nullableTrim($row['name'] ?? null) === null
            && $this->nullableTrim($row['unit'] ?? null) === null
            && $this->nullableTrim($row['component_cost'] ?? null) === null
            && $this->nullableTrim($row['serial_number'] ?? null) === null
            && $this->nullableTrim($row['condition'] ?? null) === null
            && $this->nullableTrim($row['remarks'] ?? null) === null
            && (! array_key_exists('is_present', $row) || filter_var($row['is_present'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false);
    }

    public function moneyAsFloat(mixed $value): float
    {
        return round($this->moneyToCents($value) / 100, 2);
    }

    public function moneyToCents(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return (int) round(((float) $value) * 100);
    }

    public function centsToDecimalString(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    public function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
