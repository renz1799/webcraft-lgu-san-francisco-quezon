<?php

namespace App\Modules\GSO\Services\Air;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Repositories\Contracts\AirItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use App\Modules\GSO\Services\AssetComponentService;
use App\Modules\GSO\Support\Air\AirStatuses;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AirInspectionItemComponentService
{
    public function __construct(
        private readonly AirRepositoryInterface $airs,
        private readonly AirItemRepositoryInterface $airItems,
        private readonly AssetComponentService $components,
        private readonly AuditLogServiceInterface $auditLogs,
    ) {}

    public function save(
        string $actorUserId,
        string $airId,
        string $lineId,
        array $rows,
    ): void {
        [$air, $airItem] = $this->resolveLineage($airId, $lineId);
        $this->assertUsesItemLevelInspectionComponents($airItem);

        $meaningfulRows = $this->filterMeaningfulRows($rows);
        $acceptedQty = max(0, (int) ($airItem->qty_accepted ?? 0));

        if ($acceptedQty <= 0 && $meaningfulRows !== []) {
            throw ValidationException::withMessages([
                'qty_accepted' => ['Save an accepted quantity before encoding inspection components.'],
            ]);
        }

        DB::transaction(function () use ($actorUserId, $air, $airItem, $meaningfulRows): void {
            $before = $this->snapshotComponents($airItem);
            $normalized = $this->validateComponentRows($meaningfulRows, $airItem);

            $this->components->syncAirItemComponents($airItem, $normalized);
            $airItem->load('components');
            $after = $this->snapshotComponents($airItem);

            $this->auditLogs->record(
                action: 'gso.air.inspection.item-components.saved',
                subject: $airItem,
                changesOld: $before,
                changesNew: $after,
                meta: [
                    'actor_user_id' => $actorUserId,
                    'air_id' => (string) $air->id,
                    'component_count' => count($after['components'] ?? []),
                ],
                message: 'AIR item components saved: ' . $this->airItemLabel($airItem),
                display: [
                    'summary' => 'AIR item components saved: ' . $this->airItemLabel($airItem),
                    'subject_label' => $this->airItemLabel($airItem),
                    'sections' => [[
                        'title' => 'Inspection Components',
                        'items' => [
                            ['label' => 'AIR', 'before' => $this->airLabel($air), 'after' => $this->airLabel($air)],
                            ['label' => 'Component Count', 'before' => (string) count($before['components'] ?? []), 'after' => (string) count($after['components'] ?? [])],
                        ],
                    ]],
                ],
            );
        });
    }

    /**
     * @return array{0: Air, 1: AirItem}
     */
    private function resolveLineage(string $airId, string $lineId): array
    {
        $air = $this->airs->findOrFail($airId, true);

        if ($air->trashed()) {
            throw ValidationException::withMessages([
                'air' => ['Archived AIR records cannot manage inspection components.'],
            ]);
        }

        if (! in_array((string) ($air->status ?? ''), [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS], true)) {
            throw ValidationException::withMessages([
                'status' => ['This AIR is not in a state that supports inspection component work.'],
            ]);
        }

        $airItem = $this->airItems->findOrFail($lineId);

        if ((string) $airItem->air_id !== (string) $air->id) {
            throw ValidationException::withMessages([
                'air_item' => ['The selected AIR item does not belong to this AIR record.'],
            ]);
        }

        return [$air, $airItem];
    }

    private function assertUsesItemLevelInspectionComponents(AirItem $airItem): void
    {
        if ($this->requiresUnitTracking($airItem)) {
            throw ValidationException::withMessages([
                'components' => ['This AIR line stores inspection components on inspection units instead of the AIR item record.'],
            ]);
        }
    }

    private function requiresUnitTracking(AirItem $airItem): bool
    {
        $trackingType = strtolower(trim((string) ($airItem->tracking_type_snapshot ?? '')));

        return $trackingType === 'property'
            || (bool) ($airItem->requires_serial_snapshot ?? false)
            || (bool) ($airItem->is_semi_expendable_snapshot ?? false);
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function filterMeaningfulRows(array $rows): array
    {
        return collect($rows)
            ->filter(
                fn (mixed $row): bool => is_array($row) && ! $this->components->isBlankComponentRow($row),
            )
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function validateComponentRows(array $rows, AirItem $airItem): array
    {
        $normalized = $this->components->assertComponentRowsValid(
            rows: $rows,
            parentUnitCost: $airItem->acquisition_cost,
            field: 'components',
            contextLabel: 'Component schedule',
            enforceTotalMatch: false,
            requirePositiveCost: false,
        );

        return collect($normalized)
            ->map(function (array $row, int $index) use ($rows): array {
                $clientRequestId = $this->nullableString($rows[$index]['client_request_id'] ?? null);

                return array_merge($row, [
                    'client_request_id' => $clientRequestId,
                ]);
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotComponents(AirItem $airItem): array
    {
        $components = $airItem->relationLoaded('components')
            ? $airItem->components
            : $airItem->components()->get();

        return [
            'components' => $components
                ->map(function ($component): array {
                    return [
                        'id' => (string) $component->id,
                        'client_request_id' => $this->nullableString($component->client_request_id),
                        'line_no' => (int) ($component->line_no ?? 1),
                        'name' => trim((string) ($component->name ?? '')),
                        'quantity' => max(1, (int) ($component->quantity ?? 1)),
                        'unit' => $this->nullableString($component->unit),
                        'component_cost' => round((float) ($component->component_cost ?? 0), 2),
                        'serial_number' => $this->nullableString($component->serial_number),
                        'condition' => $this->nullableString($component->condition),
                        'is_present' => (bool) ($component->is_present ?? true),
                        'remarks' => $this->nullableString($component->remarks),
                    ];
                })
                ->values()
                ->all(),
        ];
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

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value === '' ? null : $value;
    }
}
