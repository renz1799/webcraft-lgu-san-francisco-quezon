<?php

namespace App\Modules\GSO\Services;

use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\InventoryItemEvent;
use App\Modules\GSO\Repositories\Contracts\InventoryItemEventRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemCardPrintServiceInterface;

class InventoryItemCardPrintService implements InventoryItemCardPrintServiceInterface
{
    public function __construct(
        private readonly InventoryItemEventRepositoryInterface $events,
    ) {}

    public function getPropertyCardPrintPayload(InventoryItem $inventoryItem, array $options = []): array
    {
        $inventoryItem->loadMissing([
            'item',
            'fundSource.fundCluster',
            'department',
        ]);

        return (bool) $inventoryItem->is_ics
            ? [
                'view' => 'gso::property-cards.ics-print',
                'data' => $this->buildIcsPayload($inventoryItem, $options),
            ]
            : [
                'view' => 'gso::property-cards.pc-print',
                'data' => $this->buildPpePayload($inventoryItem, $options),
            ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPpePayload(InventoryItem $inventoryItem, array $options): array
    {
        $balance = 0;
        $entries = $this->events->getForPropertyCard((string) $inventoryItem->id)
            ->map(function (InventoryItemEvent $event) use (&$balance): array {
                $qtyIn = (int) ($event->qty_in ?? 0);
                $qtyOut = (int) ($event->qty_out ?? 0);
                $balance = $balance + $qtyIn - $qtyOut;

                return [
                    'event_date' => $event->event_date,
                    'reference' => $this->referenceDisplay($event),
                    'qty_in' => $qtyIn > 0 ? $qtyIn : null,
                    'qty_out' => $qtyOut > 0 ? $qtyOut : null,
                    'office' => $this->officeSnapshot($event),
                    'officer' => $this->officerSnapshot($event),
                    'balance_qty' => $balance,
                    'amount_snapshot' => $event->amount_snapshot !== null ? (float) $event->amount_snapshot : null,
                    'notes' => $this->nullableString($event->notes) ?? '',
                ];
            })
            ->values()
            ->all();

        return [
            'card' => [
                'lgu' => config('app.lgu_name', 'San Francisco, Quezon'),
                'fund' => $this->nullableString($inventoryItem->fundSource?->name) ?? '-',
                'property_name' => $this->nullableString($inventoryItem->item?->item_name) ?? '-',
                'description' => $this->nullableString($inventoryItem->description) ?? '-',
                'reference' => $this->nullableString($inventoryItem->property_number)
                    ?? $this->nullableString($inventoryItem->stock_number)
                    ?? (string) $inventoryItem->id,
                'starting_balance_qty' => 0,
            ],
            'entries' => $entries,
            'maxGridRows' => 18,
            'isPreview' => (bool) ($options['preview'] ?? false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIcsPayload(InventoryItem $inventoryItem, array $options): array
    {
        $balance = 0;
        $entries = $this->events->getForPropertyCard((string) $inventoryItem->id)
            ->map(function (InventoryItemEvent $event) use ($inventoryItem, &$balance): array {
                $qtyIn = (int) ($event->qty_in ?? 0);
                $qtyOut = (int) ($event->qty_out ?? 0);
                $balance = $balance + $qtyIn - $qtyOut;
                $unitCost = $event->amount_snapshot !== null ? (float) $event->amount_snapshot : null;

                return [
                    'event_date' => $event->event_date,
                    'reference' => $this->referenceDisplay($event),
                    'qty_in' => $qtyIn > 0 ? $qtyIn : null,
                    'qty_out' => $qtyOut > 0 ? $qtyOut : null,
                    'receipt_unit_cost' => $qtyIn > 0 ? $unitCost : null,
                    'receipt_total_cost' => $qtyIn > 0 && $unitCost !== null ? ($qtyIn * $unitCost) : null,
                    'issue_amount' => $qtyOut > 0 && $unitCost !== null ? ($qtyOut * $unitCost) : null,
                    'item_no' => $this->nullableString($inventoryItem->stock_number),
                    'office' => $this->officeSnapshot($event),
                    'officer' => $this->officerSnapshot($event),
                    'balance_qty' => $balance,
                    'notes' => $this->nullableString($event->notes) ?? '',
                ];
            })
            ->values()
            ->all();

        return [
            'card' => [
                'entity_name' => config('app.lgu_name', 'San Francisco, Quezon'),
                'lgu' => config('app.lgu_name', 'San Francisco, Quezon'),
                'fund_cluster' => $this->nullableString($inventoryItem->fundSource?->fundCluster?->code) ?? '-',
                'fund' => $this->nullableString($inventoryItem->fundSource?->name) ?? '-',
                'property_name' => $this->nullableString($inventoryItem->item?->item_name) ?? '-',
                'description' => $this->nullableString($inventoryItem->description) ?? '-',
                'reference' => $this->nullableString($inventoryItem->property_number)
                    ?? $this->nullableString($inventoryItem->stock_number)
                    ?? (string) $inventoryItem->id,
                'se_property_number' => $this->nullableString($inventoryItem->property_number)
                    ?? $this->nullableString($inventoryItem->stock_number)
                    ?? (string) $inventoryItem->id,
                'starting_balance_qty' => 0,
            ],
            'entries' => $entries,
            'maxGridRows' => 18,
            'isPreview' => (bool) ($options['preview'] ?? false),
        ];
    }

    private function referenceDisplay(InventoryItemEvent $event): string
    {
        $referenceType = $this->nullableString($event->reference_type);
        $referenceNo = $this->nullableString($event->reference_no);

        if ($referenceType !== null && $referenceNo !== null) {
            return strtoupper($referenceType) . ': ' . $referenceNo;
        }

        if ($referenceNo !== null) {
            return $referenceNo;
        }

        return $referenceType !== null ? strtoupper($referenceType) : '';
    }

    private function officeSnapshot(InventoryItemEvent $event): string
    {
        $snapshot = $this->nullableString($event->office_snapshot);
        if ($snapshot !== null) {
            return $snapshot;
        }

        $department = $event->relationLoaded('department') ? $event->department : null;
        $code = trim((string) ($department?->code ?? ''));
        $name = trim((string) ($department?->name ?? ''));

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : '');
    }

    private function officerSnapshot(InventoryItemEvent $event): string
    {
        return $this->nullableString($event->officer_snapshot)
            ?? $this->nullableString($event->person_accountable)
            ?? '';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
