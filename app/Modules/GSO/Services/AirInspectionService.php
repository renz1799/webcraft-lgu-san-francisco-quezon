<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\AirDatatableRowBuilderInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Repositories\Contracts\AirItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirItemUnitRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use App\Modules\GSO\Services\Contracts\AirInspectionServiceInterface;
use App\Modules\GSO\Support\AirStatuses;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AirInspectionService implements AirInspectionServiceInterface
{
    public function __construct(
        private readonly AirRepositoryInterface $airs,
        private readonly AirItemRepositoryInterface $airItems,
        private readonly AirItemUnitRepositoryInterface $units,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly AirDatatableRowBuilderInterface $airRowBuilder,
    ) {}

    public function getForInspection(string $airId): array
    {
        $air = $this->airs->findOrFail($airId, true);
        $items = $this->airItems->listByAirId($airId);
        $unitCounts = $this->units->countByAirItemIds(
            $items->pluck('id')->map(fn (mixed $id): string => (string) $id)->all()
        );

        return [
            'air' => $this->serializeAir($air),
            'items' => $items
                ->map(fn (AirItem $airItem): array => $this->serializeAirItem(
                    $airItem,
                    (int) ($unitCounts[(string) $airItem->id] ?? 0),
                ))
                ->values()
                ->all(),
        ];
    }

    public function saveInspection(string $actorUserId, string $airId, array $data): array
    {
        DB::transaction(function () use ($actorUserId, $airId, $data): void {
            $air = $this->assertEditableInspection($airId);
            $airItems = $this->airItems->listByAirId((string) $air->id);
            $payloadById = collect($data['items'] ?? [])
                ->filter(fn (mixed $row): bool => is_array($row) && trim((string) ($row['id'] ?? '')) !== '')
                ->keyBy(fn (array $row): string => (string) $row['id']);

            $before = $this->snapshotAir($air);
            $updatedItemCount = 0;

            foreach ($airItems as $airItem) {
                $payload = $payloadById->get((string) $airItem->id);

                if (! is_array($payload)) {
                    continue;
                }

                $this->applyInspectionItemPayload($airItem, $payload);
                $this->airItems->save($airItem);
                $updatedItemCount++;
            }

            $air->invoice_number = $this->nullableString($data['invoice_number'] ?? $air->invoice_number);
            $air->invoice_date = $data['invoice_date'] ?? $air->invoice_date?->toDateString();
            $air->date_received = $data['date_received'] ?? $air->date_received?->toDateString();
            $air->received_completeness = $this->nullableString($data['received_completeness'] ?? $air->received_completeness);
            $air->received_notes = $this->nullableString($data['received_notes'] ?? $air->received_notes);

            if ((string) $air->status === AirStatuses::SUBMITTED) {
                $air->status = AirStatuses::IN_PROGRESS;
            }

            $air = $this->airs->save($air);
            $after = $this->snapshotAir($air);

            $this->auditLogs->record(
                action: 'gso.air.inspection.saved',
                subject: $air,
                changesOld: $before,
                changesNew: $after,
                meta: [
                    'actor_user_id' => $actorUserId,
                    'updated_item_count' => $updatedItemCount,
                ],
                message: 'AIR inspection saved: ' . $this->airLabel($air),
                display: [
                    'summary' => 'AIR inspection saved: ' . $this->airLabel($air),
                    'subject_label' => $this->airLabel($air),
                    'sections' => [[
                        'title' => 'Inspection Capture',
                        'items' => [
                            ['label' => 'Invoice Number', 'before' => $this->displayValue($before['invoice_number'] ?? null), 'after' => $this->displayValue($after['invoice_number'] ?? null)],
                            ['label' => 'Date Received', 'before' => $this->displayValue($before['date_received'] ?? null), 'after' => $this->displayValue($after['date_received'] ?? null)],
                            ['label' => 'Received Completeness', 'before' => $this->displayValue($before['received_completeness'] ?? null), 'after' => $this->displayValue($after['received_completeness'] ?? null)],
                            ['label' => 'Workflow Status', 'before' => AirStatuses::label((string) ($before['status'] ?? '')), 'after' => AirStatuses::label((string) ($after['status'] ?? ''))],
                            ['label' => 'Updated Item Rows', 'before' => '0', 'after' => (string) $updatedItemCount],
                        ],
                    ]],
                ],
            );
        });

        return $this->getForInspection($airId);
    }

    public function finalizeInspection(string $actorUserId, string $airId): array
    {
        DB::transaction(function () use ($actorUserId, $airId): void {
            $air = $this->assertEditableInspection($airId);
            $airItems = $this->airItems->listByAirId((string) $air->id);

            $this->assertInspectionHeaderComplete($air);
            $this->assertInspectionItemsReadyForFinalize($airItems);

            $before = $this->snapshotAir($air);
            $air->status = AirStatuses::INSPECTED;
            $air->date_inspected = now()->toDateString();
            $air->inspection_verified = true;
            $air = $this->airs->save($air);
            $after = $this->snapshotAir($air);

            $this->auditLogs->record(
                action: 'gso.air.inspection.finalized',
                subject: $air,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'AIR inspection finalized: ' . $this->airLabel($air),
                display: [
                    'summary' => 'AIR inspection finalized: ' . $this->airLabel($air),
                    'subject_label' => $this->airLabel($air),
                    'sections' => [[
                        'title' => 'Inspection Finalization',
                        'items' => [
                            ['label' => 'Workflow Status', 'before' => AirStatuses::label((string) ($before['status'] ?? '')), 'after' => AirStatuses::label((string) ($after['status'] ?? ''))],
                            ['label' => 'Inspection Date', 'before' => $this->displayValue($before['date_inspected'] ?? null), 'after' => $this->displayValue($after['date_inspected'] ?? null)],
                            ['label' => 'Inspection Verified', 'before' => $this->displayValue($before['inspection_verified'] ?? null), 'after' => $this->displayValue($after['inspection_verified'] ?? null)],
                        ],
                    ]],
                ],
            );
        });

        return $this->getForInspection($airId);
    }

    private function assertEditableInspection(string $airId): Air
    {
        $air = $this->airs->findOrFail($airId, true);

        if ($air->trashed()) {
            throw ValidationException::withMessages([
                'air' => ['Restore this AIR before editing its inspection details.'],
            ]);
        }

        if (! in_array((string) ($air->status ?? ''), [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS], true)) {
            throw ValidationException::withMessages([
                'status' => ['Only submitted or in-progress AIR records can be edited in the inspection workspace.'],
            ]);
        }

        return $air;
    }

    private function applyInspectionItemPayload(AirItem $airItem, array $payload): void
    {
        $ordered = max(0, (int) ($airItem->qty_ordered ?? 0));
        $delivered = max(0, (int) ($payload['qty_delivered'] ?? $airItem->qty_delivered ?? 0));
        $accepted = max(0, (int) ($payload['qty_accepted'] ?? $airItem->qty_accepted ?? 0));

        $errors = [];

        if ($ordered > 0 && $delivered > $ordered) {
            $errors[] = 'Delivered quantity cannot exceed the ordered quantity.';
        }

        if ($accepted > $delivered) {
            $errors[] = 'Accepted quantity cannot exceed the delivered quantity.';
        }

        if ($ordered > 0 && $delivered < $ordered && $accepted > 0) {
            $errors[] = 'Accepted quantity must stay at 0 until the full ordered quantity is delivered.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'items.' . (string) $airItem->id => $errors,
            ]);
        }

        $airItem->description_snapshot = $this->nullableString($payload['description_snapshot'] ?? $airItem->description_snapshot);
        $airItem->remarks = $this->nullableString($payload['remarks'] ?? $airItem->remarks);
        $airItem->qty_delivered = $delivered;
        $airItem->qty_accepted = $accepted;
    }

    /**
     * @param  Collection<int, AirItem>  $airItems
     */
    private function assertInspectionItemsReadyForFinalize(Collection $airItems): void
    {
        if ($airItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['Add AIR item rows before finalizing inspection.'],
            ]);
        }

        $unitCounts = $this->units->countByAirItemIds(
            $airItems->pluck('id')->map(fn (mixed $id): string => (string) $id)->all()
        );
        $errors = [];

        foreach ($airItems as $airItem) {
            $label = $this->airItemLabel($airItem);
            $ordered = max(0, (int) ($airItem->qty_ordered ?? 0));
            $delivered = max(0, (int) ($airItem->qty_delivered ?? 0));
            $accepted = max(0, (int) ($airItem->qty_accepted ?? 0));

            if ($ordered > 0 && $delivered > $ordered) {
                $errors['items.' . (string) $airItem->id][] = "{$label}: delivered quantity cannot exceed ordered quantity.";
            }

            if ($accepted > $delivered) {
                $errors['items.' . (string) $airItem->id][] = "{$label}: accepted quantity cannot exceed delivered quantity.";
            }

            if ($ordered > 0 && $delivered < $ordered && $accepted > 0) {
                $errors['items.' . (string) $airItem->id][] = "{$label}: accepted quantity must stay at 0 until the full ordered quantity is delivered.";
            }

            if (! $this->requiresUnitTracking($airItem)) {
                continue;
            }

            $actualUnits = (int) ($unitCounts[(string) $airItem->id] ?? 0);

            if ($accepted !== $actualUnits) {
                $errors['items.' . (string) $airItem->id][] = "{$label}: unit rows must match the accepted quantity before finalizing.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function assertInspectionHeaderComplete(Air $air): void
    {
        $required = [
            'invoice_number' => 'Invoice / DR / SI No.',
            'invoice_date' => 'Invoice Date',
            'date_received' => 'Date Received',
            'received_completeness' => 'Received Completeness',
        ];

        $errors = [];

        foreach ($required as $field => $label) {
            if (blank($air->{$field})) {
                $errors[$field] = [$label . ' is required before finalizing this AIR inspection.'];
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAir(Air $air): array
    {
        $payload = $this->airRowBuilder->build($air);
        $payload['can_edit_inspection'] = ! $air->trashed()
            && in_array((string) ($air->status ?? ''), [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS], true);
        $payload['can_view_inspection'] = ! $air->trashed()
            && in_array((string) ($air->status ?? ''), [AirStatuses::SUBMITTED, AirStatuses::IN_PROGRESS, AirStatuses::INSPECTED], true);

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeAirItem(AirItem $airItem, int $unitCount): array
    {
        return [
            'id' => (string) $airItem->id,
            'air_id' => (string) $airItem->air_id,
            'item_id' => (string) $airItem->item_id,
            'item_label' => $this->airItemLabel($airItem),
            'item_name_snapshot' => $this->nullableString($airItem->item_name_snapshot),
            'stock_no_snapshot' => $this->nullableString($airItem->stock_no_snapshot),
            'description_snapshot' => $this->nullableString($airItem->description_snapshot),
            'unit_snapshot' => $this->nullableString($airItem->unit_snapshot),
            'qty_ordered' => (int) ($airItem->qty_ordered ?? 0),
            'qty_delivered' => (int) ($airItem->qty_delivered ?? 0),
            'qty_accepted' => (int) ($airItem->qty_accepted ?? 0),
            'tracking_type_snapshot' => $this->nullableString($airItem->tracking_type_snapshot) ?? 'property',
            'requires_serial_snapshot' => (bool) ($airItem->requires_serial_snapshot ?? false),
            'is_semi_expendable_snapshot' => (bool) ($airItem->is_semi_expendable_snapshot ?? false),
            'remarks' => $this->nullableString($airItem->remarks),
            'units_count' => $unitCount,
            'needs_units' => $this->requiresUnitTracking($airItem),
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
    private function snapshotAir(Air $air): array
    {
        return [
            'status' => $this->nullableString($air->status),
            'invoice_number' => $this->nullableString($air->invoice_number),
            'invoice_date' => $air->invoice_date?->toDateString(),
            'date_received' => $air->date_received?->toDateString(),
            'received_completeness' => $this->nullableString($air->received_completeness),
            'received_notes' => $this->nullableString($air->received_notes),
            'date_inspected' => $air->date_inspected?->toDateString(),
            'inspection_verified' => $air->inspection_verified === null
                ? null
                : ($air->inspection_verified ? 'Yes' : 'No'),
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

    private function displayValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
