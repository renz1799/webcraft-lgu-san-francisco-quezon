<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\InventoryItemEvent;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Repositories\Contracts\InventoryItemEventRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\InventoryStatuses;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryItemEventService implements InventoryItemEventServiceInterface
{
    public function __construct(
        private readonly InventoryItemRepositoryInterface $inventoryItems,
        private readonly InventoryItemEventRepositoryInterface $events,
        private readonly AuditLogServiceInterface $auditLogs,
    ) {}

    public function listForInventoryItem(string $inventoryItemId): array
    {
        $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId, true);

        return $this->buildPayload($inventoryItem);
    }

    public function create(string $actorUserId, string $inventoryItemId, array $data): InventoryItemEvent
    {
        return DB::transaction(function () use ($actorUserId, $inventoryItemId, $data) {
            $inventoryItem = $this->inventoryItems->findOrFail($inventoryItemId, true);

            if ($inventoryItem->trashed()) {
                throw ValidationException::withMessages([
                    'inventory_item' => ['Archived inventory items cannot record new events.'],
                ]);
            }

            $payload = $this->normalizePayload($inventoryItem, $actorUserId, $data);
            $event = $this->events->create($payload);

            $this->auditLogs->record(
                action: 'gso.inventory-item.event.created',
                subject: $event,
                changesOld: [],
                changesNew: $event->toArray(),
                meta: ['actor_user_id' => $actorUserId],
                message: 'Inventory item event recorded: ' . $this->inventoryItemLabel($inventoryItem),
                display: $this->buildCreatedDisplay($inventoryItem, $event),
            );

            return $event;
        });
    }

    /**
     * @return array{inventory_item: array<string, mixed>, events: array<int, array<string, mixed>>}
     */
    private function buildPayload(InventoryItem $inventoryItem): array
    {
        $events = $this->events->listForInventoryItem((string) $inventoryItem->id)
            ->map(fn (InventoryItemEvent $event): array => $this->mapEvent($event))
            ->values()
            ->all();

        return [
            'inventory_item' => [
                'id' => (string) $inventoryItem->id,
                'label' => $this->inventoryItemLabel($inventoryItem),
                'property_number' => $this->nullableString($inventoryItem->property_number),
                'po_number' => $this->nullableString($inventoryItem->po_number),
                'event_count' => count($events),
                'is_archived' => $inventoryItem->trashed(),
            ],
            'events' => $events,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizePayload(InventoryItem $inventoryItem, string $actorUserId, array $data): array
    {
        $eventType = trim((string) ($data['event_type'] ?? ''));
        $quantity = max(0, (int) ($data['quantity'] ?? 0));

        if (! in_array($eventType, InventoryEventTypes::values(), true)) {
            throw ValidationException::withMessages([
                'event_type' => ['Event type is invalid.'],
            ]);
        }

        if (! in_array($eventType, InventoryEventTypes::metadataOnly(), true) && $quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => ['Quantity must be greater than zero for this event type.'],
            ]);
        }

        $status = trim((string) ($data['status'] ?? $inventoryItem->status ?? ''));
        if ($status !== '' && ! in_array($status, InventoryStatuses::values(), true)) {
            throw ValidationException::withMessages([
                'status' => ['Status is invalid.'],
            ]);
        }

        $condition = trim((string) ($data['condition'] ?? $inventoryItem->condition ?? ''));
        if ($condition !== '' && ! in_array($condition, InventoryConditions::values(), true)) {
            throw ValidationException::withMessages([
                'condition' => ['Condition is invalid.'],
            ]);
        }

        $departmentId = $this->nullableString($data['department_id'] ?? $inventoryItem->department_id);
        $personAccountable = $this->nullableString($data['person_accountable'] ?? $inventoryItem->accountable_officer);

        return [
            'inventory_item_id' => (string) $inventoryItem->id,
            'department_id' => $departmentId,
            'performed_by_user_id' => $actorUserId,
            'event_type' => $eventType,
            'event_date' => $this->normalizeEventDate($data['event_date'] ?? null),
            'qty_in' => in_array($eventType, InventoryEventTypes::increasesQuantity(), true) ? $quantity : 0,
            'qty_out' => in_array($eventType, InventoryEventTypes::decreasesQuantity(), true) ? $quantity : 0,
            'amount_snapshot' => array_key_exists('amount_snapshot', $data)
                ? (($data['amount_snapshot'] === null || $data['amount_snapshot'] === '') ? null : (float) $data['amount_snapshot'])
                : ($inventoryItem->acquisition_cost !== null ? (float) $inventoryItem->acquisition_cost : null),
            'unit_snapshot' => $this->nullableString($data['unit_snapshot'] ?? $inventoryItem->unit),
            'office_snapshot' => $this->resolveOfficeSnapshot($departmentId, $data['office_snapshot'] ?? null),
            'officer_snapshot' => $this->nullableString($data['officer_snapshot'] ?? $personAccountable),
            'status' => $status !== '' ? $status : null,
            'condition' => $condition !== '' ? $condition : null,
            'person_accountable' => $personAccountable,
            'notes' => $this->nullableString($data['notes'] ?? null),
            'reference_type' => $this->nullableString($data['reference_type'] ?? null),
            'reference_no' => $this->nullableString($data['reference_no'] ?? null),
            'reference_id' => $this->nullableString($data['reference_id'] ?? null),
            'fund_source_id' => $this->nullableString($data['fund_source_id'] ?? $inventoryItem->fund_source_id),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapEvent(InventoryItemEvent $event): array
    {
        $department = $event->relationLoaded('department') ? $event->department : null;
        $performedBy = $event->relationLoaded('performedBy') ? $event->performedBy : null;
        $fundSource = $event->relationLoaded('fundSource') ? $event->fundSource : null;
        $files = $event->relationLoaded('files') ? $event->files : collect();

        return [
            'id' => (string) $event->id,
            'event_type' => (string) $event->event_type,
            'event_type_text' => InventoryEventTypes::labels()[(string) $event->event_type] ?? 'Unknown',
            'event_date' => $event->event_date?->toDateTimeString(),
            'event_date_text' => $event->event_date?->format('M d, Y h:i A') ?? '-',
            'qty_in' => (int) ($event->qty_in ?? 0),
            'qty_out' => (int) ($event->qty_out ?? 0),
            'movement_text' => $this->movementText($event),
            'amount_snapshot' => $event->amount_snapshot !== null ? (string) $event->amount_snapshot : null,
            'amount_snapshot_text' => $event->amount_snapshot !== null ? number_format((float) $event->amount_snapshot, 2) : '-',
            'unit_snapshot' => $this->nullableString($event->unit_snapshot),
            'office_snapshot' => $this->nullableString($event->office_snapshot),
            'officer_snapshot' => $this->nullableString($event->officer_snapshot),
            'status' => $this->nullableString($event->status),
            'status_text' => $this->labelOrNone($event->status, InventoryStatuses::labels()),
            'condition' => $this->nullableString($event->condition),
            'condition_text' => $this->labelOrNone($event->condition, InventoryConditions::labels()),
            'person_accountable' => $this->nullableString($event->person_accountable),
            'notes' => $this->nullableString($event->notes),
            'reference_type' => $this->nullableString($event->reference_type),
            'reference_no' => $this->nullableString($event->reference_no),
            'reference_id' => $this->nullableString($event->reference_id),
            'reference_label' => $this->referenceLabel($event->reference_type, $event->reference_no),
            'department_label' => $department
                ? $this->entityLabel((string) $department->code, (string) $department->name)
                : ($this->nullableString($event->office_snapshot) ?? 'None'),
            'performed_by_label' => $this->userLabel($performedBy?->username, $performedBy?->email),
            'fund_source_label' => $fundSource
                ? $this->entityLabel((string) $fundSource->code, (string) $fundSource->name)
                : 'None',
            'file_count' => $files->count(),
            'is_archived' => $event->trashed(),
        ];
    }

    private function buildCreatedDisplay(InventoryItem $inventoryItem, InventoryItemEvent $event): array
    {
        return [
            'summary' => 'Inventory event recorded: ' . $this->inventoryItemLabel($inventoryItem),
            'subject_label' => $this->inventoryItemLabel($inventoryItem),
            'sections' => [[
                'title' => 'Event Details',
                'items' => [
                    ['label' => 'Event Type', 'before' => 'None', 'after' => InventoryEventTypes::labels()[(string) $event->event_type] ?? 'Unknown'],
                    ['label' => 'Event Date', 'before' => 'None', 'after' => $event->event_date?->format('M d, Y h:i A') ?? 'None'],
                    ['label' => 'Reference', 'before' => 'None', 'after' => $this->referenceLabel($event->reference_type, $event->reference_no) ?? 'None'],
                    ['label' => 'Movement', 'before' => 'None', 'after' => $this->movementText($event)],
                ],
            ]],
        ];
    }

    private function inventoryItemLabel(InventoryItem $inventoryItem): string
    {
        $item = $inventoryItem->relationLoaded('item') ? $inventoryItem->item : Item::query()
            ->withTrashed()
            ->select(['id', 'item_name'])
            ->find($inventoryItem->item_id);

        $itemName = trim((string) ($item?->item_name ?? ''));
        $propertyNumber = trim((string) ($inventoryItem->property_number ?? ''));

        if ($itemName !== '' && $propertyNumber !== '') {
            return "{$itemName} ({$propertyNumber})";
        }

        return $itemName !== '' ? $itemName : ($propertyNumber !== '' ? $propertyNumber : 'Inventory Item');
    }

    private function normalizeEventDate(mixed $value): CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        $value = trim((string) ($value ?? ''));

        return $value !== '' ? Carbon::parse($value) : now();
    }

    private function resolveOfficeSnapshot(?string $departmentId, mixed $override): ?string
    {
        $override = $this->nullableString($override);

        if ($override !== null) {
            return $override;
        }

        $departmentId = trim((string) ($departmentId ?? ''));

        if ($departmentId === '') {
            return null;
        }

        $department = Department::query()
            ->withTrashed()
            ->select(['id', 'code', 'name'])
            ->find($departmentId);

        if (! $department) {
            return null;
        }

        return $this->entityLabel((string) $department->code, (string) $department->name);
    }

    private function movementText(InventoryItemEvent $event): string
    {
        if ((int) ($event->qty_in ?? 0) > 0) {
            return '+' . (int) $event->qty_in;
        }

        if ((int) ($event->qty_out ?? 0) > 0) {
            return '-' . (int) $event->qty_out;
        }

        return 'No quantity change';
    }

    private function referenceLabel(mixed $referenceType, mixed $referenceNo): ?string
    {
        $referenceType = trim((string) ($referenceType ?? ''));
        $referenceNo = trim((string) ($referenceNo ?? ''));

        if ($referenceType === '' && $referenceNo === '') {
            return null;
        }

        if ($referenceType === '') {
            return $referenceNo;
        }

        if ($referenceNo === '') {
            return strtoupper($referenceType);
        }

        return strtoupper($referenceType) . ': ' . $referenceNo;
    }

    private function labelOrNone(mixed $value, array $labels): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? ($labels[$value] ?? $value) : 'None';
    }

    private function entityLabel(string $code, string $name): string
    {
        $code = trim($code);
        $name = trim($name);

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'None');
    }

    private function userLabel(mixed $username, mixed $email): string
    {
        $username = trim((string) ($username ?? ''));
        $email = trim((string) ($email ?? ''));

        if ($username !== '' && $email !== '') {
            return "{$username} ({$email})";
        }

        return $username !== '' ? $username : ($email !== '' ? $email : 'System');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
