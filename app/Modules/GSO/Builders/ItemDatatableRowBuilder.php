<?php

namespace App\Modules\GSO\Builders;

use App\Modules\GSO\Builders\Contracts\ItemDatatableRowBuilderInterface;
use App\Modules\GSO\Models\Item;

class ItemDatatableRowBuilder implements ItemDatatableRowBuilderInterface
{
    public function build(Item $item): array
    {
        $asset = $item->relationLoaded('asset') ? $item->asset : null;
        $unitConversions = $item->relationLoaded('unitConversions')
            ? $item->unitConversions
                ->map(fn ($conversion): array => [
                    'id' => (string) $conversion->id,
                    'from_unit' => (string) $conversion->from_unit,
                    'multiplier' => (int) $conversion->multiplier,
                ])
                ->values()
                ->all()
            : [];
        $isArchived = $item->deleted_at !== null;

        return [
            'id' => (string) $item->id,
            'asset_id' => (string) $item->asset_id,
            'asset_label' => $asset ? $this->assetLabel((string) $asset->asset_code, (string) $asset->asset_name) : 'Unknown Asset Category',
            'asset' => $asset ? [
                'id' => (string) $asset->id,
                'asset_code' => (string) $asset->asset_code,
                'asset_name' => (string) $asset->asset_name,
            ] : null,
            'item_name' => (string) $item->item_name,
            'description' => $this->nullableString($item->description),
            'base_unit' => $this->nullableString($item->base_unit),
            'item_identification' => $this->nullableString($item->item_identification),
            'major_sub_account_group' => $this->nullableString($item->major_sub_account_group),
            'tracking_type' => (string) $item->tracking_type,
            'tracking_type_text' => $this->trackingTypeLabel((string) $item->tracking_type),
            'requires_serial' => (bool) $item->requires_serial,
            'requires_serial_text' => $item->requires_serial ? 'Yes' : 'No',
            'is_semi_expendable' => (bool) $item->is_semi_expendable,
            'is_semi_expendable_text' => $item->is_semi_expendable ? 'Yes' : 'No',
            'is_selected' => (bool) $item->is_selected,
            'created_at' => $item->created_at?->toDateTimeString(),
            'created_at_text' => $item->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $item->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $item->deleted_at?->format('M d, Y h:i A'),
            'status' => $isArchived ? 'archived' : 'active',
            'is_archived' => $isArchived,
            'label' => $this->itemLabel($item),
            'unit_conversions' => $unitConversions,
        ];
    }

    private function assetLabel(string $assetCode, string $assetName): string
    {
        $assetCode = trim($assetCode);
        $assetName = trim($assetName);

        if ($assetCode !== '' && $assetName !== '') {
            return "{$assetCode} - {$assetName}";
        }

        return $assetCode !== '' ? $assetCode : ($assetName !== '' ? $assetName : 'Asset Category');
    }

    private function itemLabel(Item $item): string
    {
        $itemName = trim((string) $item->item_name);
        $identification = trim((string) ($item->item_identification ?? ''));

        if ($itemName !== '' && $identification !== '') {
            return "{$itemName} ({$identification})";
        }

        return $itemName !== '' ? $itemName : ($identification !== '' ? $identification : 'Item');
    }

    private function trackingTypeLabel(string $value): string
    {
        return match (trim($value)) {
            'property' => 'Property',
            'consumable' => 'Consumable',
            default => 'Unknown',
        };
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
