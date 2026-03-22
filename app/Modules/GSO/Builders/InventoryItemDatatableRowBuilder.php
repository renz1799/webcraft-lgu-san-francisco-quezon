<?php

namespace App\Modules\GSO\Builders;

use App\Modules\GSO\Builders\Contracts\InventoryItemDatatableRowBuilderInterface;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryStatuses;

class InventoryItemDatatableRowBuilder implements InventoryItemDatatableRowBuilderInterface
{
    public function build(InventoryItem $inventoryItem): array
    {
        $item = $inventoryItem->relationLoaded('item') ? $inventoryItem->item : null;
        $department = $inventoryItem->relationLoaded('department') ? $inventoryItem->department : null;
        $fundSource = $inventoryItem->relationLoaded('fundSource') ? $inventoryItem->fundSource : null;
        $accountableOfficer = $inventoryItem->relationLoaded('accountableOfficerRelation')
            ? $inventoryItem->accountableOfficerRelation
            : null;
        $isArchived = $inventoryItem->deleted_at !== null;
        $publicAssetCode = $this->nullableString($inventoryItem->property_number)
            ?? $this->nullableString($inventoryItem->stock_number)
            ?? (string) $inventoryItem->id;

        return [
            'id' => (string) $inventoryItem->id,
            'item_id' => (string) $inventoryItem->item_id,
            'item_label' => $this->itemLabel(
                itemName: (string) ($item->item_name ?? ''),
                identification: (string) ($item->item_identification ?? ''),
            ),
            'item' => $item ? [
                'id' => (string) $item->id,
                'item_name' => (string) $item->item_name,
                'item_identification' => $this->nullableString($item->item_identification),
            ] : null,
            'department_id' => $this->nullableString($inventoryItem->department_id),
            'department_label' => $department
                ? $this->departmentLabel((string) $department->code, (string) $department->name)
                : 'None',
            'fund_source_id' => $this->nullableString($inventoryItem->fund_source_id),
            'fund_source_label' => $fundSource
                ? $this->fundSourceLabel((string) $fundSource->code, (string) $fundSource->name)
                : 'None',
            'property_number' => $this->nullableString($inventoryItem->property_number),
            'acquisition_date' => $inventoryItem->acquisition_date?->toDateString(),
            'acquisition_date_text' => $inventoryItem->acquisition_date?->format('M d, Y') ?? '-',
            'acquisition_cost' => $inventoryItem->acquisition_cost !== null
                ? (string) $inventoryItem->acquisition_cost
                : null,
            'acquisition_cost_text' => $inventoryItem->acquisition_cost !== null
                ? number_format((float) $inventoryItem->acquisition_cost, 2)
                : '-',
            'description' => $this->nullableString($inventoryItem->description),
            'quantity' => (int) ($inventoryItem->quantity ?? 0),
            'unit' => $this->nullableString($inventoryItem->unit),
            'stock_number' => $this->nullableString($inventoryItem->stock_number),
            'service_life' => $inventoryItem->service_life !== null ? (int) $inventoryItem->service_life : null,
            'is_ics' => (bool) $inventoryItem->is_ics,
            'classification' => (bool) $inventoryItem->is_ics ? 'ics' : 'ppe',
            'classification_text' => (bool) $inventoryItem->is_ics ? 'ICS' : 'PPE',
            'accountable_officer_id' => $this->nullableString($inventoryItem->accountable_officer_id),
            'accountable_officer' => $this->nullableString($inventoryItem->accountable_officer),
            'accountable_officer_label' => $this->nullableString($inventoryItem->accountable_officer)
                ?? $this->nullableString($accountableOfficer?->full_name)
                ?? 'None',
            'custody_state' => (string) ($inventoryItem->custody_state ?? ''),
            'custody_state_text' => InventoryCustodyStates::labels()[(string) ($inventoryItem->custody_state ?? '')] ?? 'Unknown',
            'status' => (string) ($inventoryItem->status ?? ''),
            'status_text' => InventoryStatuses::labels()[(string) ($inventoryItem->status ?? '')] ?? 'Unknown',
            'condition' => (string) ($inventoryItem->condition ?? ''),
            'condition_text' => InventoryConditions::labels()[(string) ($inventoryItem->condition ?? '')] ?? 'Unknown',
            'file_count' => (int) ($inventoryItem->files_count ?? 0),
            'event_count' => (int) ($inventoryItem->events_count ?? 0),
            'public_asset_code' => $publicAssetCode,
            'public_asset_url' => route('gso.public-assets.show', ['code' => $publicAssetCode]),
            'property_card_print_url' => route('gso.inventory-items.property-card.print', [
                'inventoryItem' => $inventoryItem->id,
                'preview' => 1,
            ]),
            'brand' => $this->nullableString($inventoryItem->brand),
            'model' => $this->nullableString($inventoryItem->model),
            'serial_number' => $this->nullableString($inventoryItem->serial_number),
            'po_number' => $this->nullableString($inventoryItem->po_number),
            'drive_folder_id' => $this->nullableString($inventoryItem->drive_folder_id),
            'remarks' => $this->nullableString($inventoryItem->remarks),
            'air_item_unit_id' => $this->nullableString($inventoryItem->air_item_unit_id),
            'created_at' => $inventoryItem->created_at?->toDateTimeString(),
            'created_at_text' => $inventoryItem->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $inventoryItem->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $inventoryItem->deleted_at?->format('M d, Y h:i A'),
            'status_mode' => $isArchived ? 'archived' : 'active',
            'is_archived' => $isArchived,
            'label' => $this->inventoryItemLabel(
                itemName: (string) ($item->item_name ?? ''),
                propertyNumber: (string) ($inventoryItem->property_number ?? ''),
            ),
        ];
    }

    private function itemLabel(string $itemName, string $identification): string
    {
        $itemName = trim($itemName);
        $identification = trim($identification);

        if ($itemName !== '' && $identification !== '') {
            return "{$itemName} ({$identification})";
        }

        return $itemName !== '' ? $itemName : ($identification !== '' ? $identification : 'Item');
    }

    private function inventoryItemLabel(string $itemName, string $propertyNumber): string
    {
        $itemName = trim($itemName);
        $propertyNumber = trim($propertyNumber);

        if ($itemName !== '' && $propertyNumber !== '') {
            return "{$itemName} ({$propertyNumber})";
        }

        return $itemName !== '' ? $itemName : ($propertyNumber !== '' ? $propertyNumber : 'Inventory Item');
    }

    private function departmentLabel(string $code, string $name): string
    {
        $code = trim($code);
        $name = trim($name);

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Department');
    }

    private function fundSourceLabel(string $code, string $name): string
    {
        $code = trim($code);
        $name = trim($name);

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Fund Source');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
