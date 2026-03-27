<?php

namespace App\Modules\GSO\Services\WMR;

use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Models\WmrItem;
use App\Modules\GSO\Repositories\Contracts\WMR\WmrItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\WMR\WmrRepositoryInterface;
use App\Modules\GSO\Services\Contracts\WMR\WmrItemServiceInterface;
use Illuminate\Support\Facades\DB;

class WmrItemService implements WmrItemServiceInterface
{
    public function __construct(
        private readonly WmrRepositoryInterface $wmrs,
        private readonly WmrItemRepositoryInterface $wmrItems,
    ) {}

    public function suggestItems(string $wmrId, string $query): array
    {
        $wmr = $this->wmrs->findOrFail($wmrId);
        abort_if((string) $wmr->status !== 'draft', 409, 'You can only add disposal items while WMR is draft.');

        return $this->wmrItems->suggestDisposableItems($wmrId, $query, 12);
    }

    public function listForEdit(string $wmrId): array
    {
        $this->wmrs->findOrFail($wmrId);

        return $this->wmrItems->listByWmrId($wmrId)
            ->map(function (WmrItem $item) {
                return [
                    'id' => (string) $item->id,
                    'inventory_item_id' => (string) $item->inventory_item_id,
                    'line_no' => (int) ($item->line_no ?? 0),
                    'quantity' => (int) ($item->quantity ?? 1),
                    'unit' => (string) ($item->unit_snapshot ?? ''),
                    'reference_no' => (string) ($item->reference_no_snapshot ?? $item->inventoryItem?->property_number ?? $item->inventoryItem?->stock_number ?? ''),
                    'item_name' => (string) ($item->item_name_snapshot ?? $item->inventoryItem?->item?->item_name ?? ''),
                    'description' => (string) ($item->description_snapshot ?? ''),
                    'date_acquired' => $item->date_acquired_snapshot ? $item->date_acquired_snapshot->toDateString() : null,
                    'acquisition_cost' => $item->acquisition_cost_snapshot !== null ? (float) $item->acquisition_cost_snapshot : null,
                    'condition' => (string) ($item->condition_snapshot ?? ''),
                    'disposal_method' => (string) ($item->disposal_method ?? ''),
                    'transfer_entity_name' => (string) ($item->transfer_entity_name ?? ''),
                    'official_receipt_no' => (string) ($item->official_receipt_no ?? ''),
                    'official_receipt_date' => $item->official_receipt_date ? $item->official_receipt_date->toDateString() : null,
                    'official_receipt_amount' => $item->official_receipt_amount !== null ? (float) $item->official_receipt_amount : null,
                ];
            })
            ->values()
            ->all();
    }

    public function addItem(string $actorUserId, string $wmrId, string $inventoryItemId): WmrItem
    {
        return DB::transaction(function () use ($wmrId, $inventoryItemId) {
            $wmr = $this->wmrs->findOrFail($wmrId);
            abort_if((string) $wmr->status !== 'draft', 409, 'You can only add disposal items while WMR is draft.');

            return $this->wmrItems->addInventoryItemToWmr($wmrId, $inventoryItemId)->refresh();
        });
    }

    public function updateItem(string $actorUserId, string $wmrId, string $wmrItemId, array $payload): WmrItem
    {
        return DB::transaction(function () use ($wmrId, $wmrItemId, $payload) {
            $wmr = $this->wmrs->findOrFail($wmrId);
            abort_if((string) $wmr->status !== 'draft', 409, 'You can only edit disposal items while WMR is draft.');

            $item = $this->wmrItems->findById($wmrItemId);
            abort_if(!$item, 404, 'WMR item not found.');
            abort_unless((string) $item->wmr_id === (string) $wmrId, 404);

            $disposalMethod = trim((string) ($payload['disposal_method'] ?? ''));
            $normalized = [
                'quantity' => max(1, (int) ($payload['quantity'] ?? 1)),
                'disposal_method' => $disposalMethod !== '' ? $disposalMethod : null,
                'transfer_entity_name' => $disposalMethod === 'transferred_without_cost'
                    ? (trim((string) ($payload['transfer_entity_name'] ?? '')) ?: null)
                    : null,
                'official_receipt_no' => trim((string) ($payload['official_receipt_no'] ?? '')) ?: null,
                'official_receipt_date' => trim((string) ($payload['official_receipt_date'] ?? '')) ?: null,
                'official_receipt_amount' => $payload['official_receipt_amount'] !== null && $payload['official_receipt_amount'] !== ''
                    ? $payload['official_receipt_amount']
                    : null,
            ];

            return $this->wmrItems->updateLine($item, $normalized);
        });
    }

    public function removeItem(string $actorUserId, string $wmrId, string $wmrItemId): void
    {
        DB::transaction(function () use ($wmrId, $wmrItemId) {
            $wmr = $this->wmrs->findOrFail($wmrId);
            abort_if((string) $wmr->status !== 'draft', 409, 'You can only remove disposal items while WMR is draft.');

            $item = $this->wmrItems->findById($wmrItemId);
            abort_if(!$item, 404, 'WMR item not found.');
            abort_unless((string) $item->wmr_id === (string) $wmrId, 404);

            $this->wmrItems->delete($item);
        });
    }
}

