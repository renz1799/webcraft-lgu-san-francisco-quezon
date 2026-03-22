<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\AssetCategory;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\AirItemUnit;
use App\Modules\GSO\Models\AirItemUnitFile;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Models\StockMovement;
use App\Modules\GSO\Repositories\Contracts\InventoryItemFileRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\StockRepositoryInterface;
use App\Modules\GSO\Services\Contracts\AirInventoryPromotionServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Support\AirStatuses;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\InventoryFileTypes;
use App\Modules\GSO\Support\InventoryStatuses;
use App\Modules\GSO\Support\StockMovementTypes;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AirInventoryPromotionService implements AirInventoryPromotionServiceInterface
{
    public function __construct(
        private readonly InventoryItemRepositoryInterface $inventoryItems,
        private readonly InventoryItemFileRepositoryInterface $inventoryFiles,
        private readonly InventoryItemEventServiceInterface $events,
        private readonly StockRepositoryInterface $stocks,
        private readonly StockMovementRepositoryInterface $stockMovements,
        private readonly AssetComponentService $components,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly GoogleDriveFolderServiceInterface $driveFolders,
        private readonly GoogleDriveFileServiceInterface $driveFiles,
    ) {}

    public function getEligibility(string $airId): array
    {
        $air = $this->findPromotableAir($airId);
        $propertyCandidates = $this->candidatePropertyUnits((string) $air->id);
        [$propertyUnits, $blockedPropertyUnits] = $propertyCandidates->partition(
            fn (AirItemUnit $unit): bool => $this->resolvePropertyPromotionBlockReason($unit) === null
        );
        $propertyUnits = $propertyUnits->values();
        $blockedPropertyUnits = $blockedPropertyUnits->values();
        $consumables = $this->eligibleConsumables((string) $air->id);

        return [
            'air' => [
                'id' => (string) $air->id,
                'label' => $this->airLabel($air),
                'status' => (string) ($air->status ?? ''),
                'status_text' => AirStatuses::label((string) ($air->status ?? '')),
                'po_number' => $this->nullableString($air->po_number),
            ],
            'property_units' => $propertyUnits
                ->map(fn (AirItemUnit $unit): array => $this->serializePropertyEligibility($unit))
                ->values()
                ->all(),
            'blocked_property_units' => $blockedPropertyUnits
                ->map(fn (AirItemUnit $unit): array => $this->serializePropertyEligibility($unit))
                ->values()
                ->all(),
            'consumables' => $consumables
                ->map(fn (AirItem $airItem): array => $this->serializeConsumableEligibility($airItem))
                ->values()
                ->all(),
            'summary' => [
                'property_units_count' => $propertyUnits->count(),
                'blocked_property_units_count' => $blockedPropertyUnits->count(),
                'consumable_lines_count' => $consumables->count(),
                'consumable_qty_accepted' => $consumables->sum(fn (AirItem $airItem): int => (int) ($airItem->qty_accepted ?? 0)),
            ],
        ];
    }

    public function promote(
        string $actorUserId,
        string $airId,
        array $airItemUnitIds = [],
        ?string $actorName = null,
    ): array {
        $air = $this->findPromotableAir($airId);

        return DB::transaction(function () use ($actorUserId, $air, $airItemUnitIds, $actorName): array {
            $departmentId = $this->resolvePoolDepartmentId($air);
            [$poolOfficerId, $poolOfficerName] = $this->resolvePoolOfficer($air, $departmentId);
            $fundSourceId = $this->resolveFundSourceId($air);
            $acquisitionDate = $this->resolveAcquisitionDate($air);

            $eligiblePropertyUnits = $this->eligiblePropertyUnits((string) $air->id)->keyBy(
                fn (AirItemUnit $unit): string => (string) $unit->id
            );

            $selectedIds = collect($airItemUnitIds)
                ->map(fn (mixed $value): string => trim((string) $value))
                ->filter()
                ->values();

            if ($selectedIds->isNotEmpty()) {
                $invalidIds = $selectedIds
                    ->reject(fn (string $unitId): bool => $eligiblePropertyUnits->has($unitId))
                    ->values();

                if ($invalidIds->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'air_item_unit_ids' => ['One or more selected AIR inspection units are no longer eligible for promotion.'],
                    ]);
                }
            }

            $unitsToPromote = $selectedIds->isNotEmpty()
                ? $selectedIds->map(fn (string $unitId): AirItemUnit => $eligiblePropertyUnits->get($unitId))->values()
                : $eligiblePropertyUnits->values();

            $propertyCreated = 0;
            $copiedFiles = 0;
            $copiedComponents = 0;
            $createdInventoryItemIds = [];

            foreach ($unitsToPromote as $unit) {
                $promotion = $this->promotePropertyUnit(
                    actorUserId: $actorUserId,
                    air: $air,
                    unit: $unit,
                    departmentId: $departmentId,
                    fundSourceId: $fundSourceId,
                    acquisitionDate: $acquisitionDate,
                    poolOfficerId: $poolOfficerId,
                    poolOfficerName: $poolOfficerName,
                );
                $inventoryItem = $promotion['inventory_item'];

                $propertyCreated++;
                $createdInventoryItemIds[] = (string) $inventoryItem->id;
                $copiedComponents += (int) ($promotion['components_copied'] ?? 0);
                $copiedFiles += $this->copyUnitFilesToInventoryItem($unit, $inventoryItem);
            }

            $propertySkipped = max(0, $eligiblePropertyUnits->count() - $unitsToPromote->count());
            [$consumablePosted, $consumableSkipped] = $this->promoteConsumables(
                actorUserId: $actorUserId,
                actorName: $actorName,
                air: $air,
                fundSourceId: $fundSourceId,
            );

            $result = [
                'property_created' => $propertyCreated,
                'property_skipped' => $propertySkipped,
                'consumable_posted' => $consumablePosted,
                'consumable_skipped' => $consumableSkipped,
                'inventory_item_ids' => $createdInventoryItemIds,
                'copied_files' => $copiedFiles,
                'components_copied' => $copiedComponents,
            ];

            $this->auditLogs->record(
                action: 'gso.air.inventory.promoted',
                subject: $air,
                changesOld: [],
                changesNew: $result,
                meta: [
                    'actor_user_id' => $actorUserId,
                    'selected_air_item_unit_ids' => $selectedIds->all(),
                ],
                message: 'AIR promoted into inventory and stock: ' . $this->airLabel($air),
                display: [
                    'summary' => 'AIR promoted into inventory and stock: ' . $this->airLabel($air),
                    'subject_label' => $this->airLabel($air),
                    'sections' => [[
                        'title' => 'Promotion Summary',
                        'items' => [
                            ['label' => 'Property Created', 'before' => '0', 'after' => (string) $propertyCreated],
                            ['label' => 'Property Skipped', 'before' => '0', 'after' => (string) $propertySkipped],
                            ['label' => 'Consumables Posted', 'before' => '0', 'after' => (string) $consumablePosted],
                            ['label' => 'Consumables Skipped', 'before' => '0', 'after' => (string) $consumableSkipped],
                            ['label' => 'Copied Unit Files', 'before' => '0', 'after' => (string) $copiedFiles],
                            ['label' => 'Copied Components', 'before' => '0', 'after' => (string) $copiedComponents],
                        ],
                    ]],
                ],
            );

            return $result;
        });
    }

    /**
     * @return array{inventory_item: InventoryItem, components_copied: int}
     */
    private function promotePropertyUnit(
        string $actorUserId,
        Air $air,
        AirItemUnit $unit,
        string $departmentId,
        string $fundSourceId,
        string $acquisitionDate,
        ?string $poolOfficerId,
        ?string $poolOfficerName,
    ): array {
        $airItem = $unit->airItem;
        $item = $airItem?->item;

        if (! $airItem || ! $item) {
            throw ValidationException::withMessages([
                'promotion' => ['One or more selected AIR units no longer have a valid linked item record.'],
            ]);
        }

        if ($this->unitAlreadyPromoted($unit)) {
            throw ValidationException::withMessages([
                'promotion' => ['One or more selected AIR units are already linked to inventory.'],
            ]);
        }

        $isIcs = $this->isIcsClassification($airItem);
        $propertyNumber = $this->resolvePropertyNumber($unit, $item, $isIcs, $acquisitionDate);

        $inventoryItem = $this->inventoryItems->create([
            'item_id' => (string) $airItem->item_id,
            'air_item_unit_id' => (string) $unit->id,
            'department_id' => $departmentId,
            'fund_source_id' => $fundSourceId,
            'property_number' => $propertyNumber,
            'acquisition_date' => $acquisitionDate,
            'acquisition_cost' => $airItem->acquisition_cost !== null ? (float) $airItem->acquisition_cost : null,
            'description' => $this->nullableString($airItem->description_snapshot),
            'quantity' => 1,
            'unit' => $this->nullableString($airItem->unit_snapshot),
            'stock_number' => $this->nullableString($airItem->stock_no_snapshot),
            'service_life' => null,
            'is_ics' => $isIcs,
            'accountable_officer' => $poolOfficerName,
            'accountable_officer_id' => $poolOfficerId,
            'custody_state' => InventoryCustodyStates::POOL,
            'status' => $this->normalizeInventoryStatus($unit->condition_status),
            'condition' => $this->normalizeInventoryCondition($unit->condition_status),
            'brand' => $this->nullableString($unit->brand),
            'model' => $this->nullableString($unit->model),
            'serial_number' => $this->nullableString($unit->serial_number),
            'po_number' => $this->nullableString($air->po_number),
            'drive_folder_id' => null,
            'remarks' => $this->nullableString($unit->condition_notes),
        ]);

        $unit->inventory_item_id = (string) $inventoryItem->id;
        $unit->property_number = $propertyNumber;
        $unit->save();

        $this->events->create($actorUserId, (string) $inventoryItem->id, [
            'event_type' => InventoryEventTypes::ACQUIRED,
            'event_date' => Carbon::parse($acquisitionDate)->endOfDay()->toDateTimeString(),
            'quantity' => 1,
            'department_id' => $departmentId,
            'person_accountable' => $poolOfficerName,
            'status' => $inventoryItem->status,
            'condition' => $inventoryItem->condition,
            'reference_type' => 'AIR',
            'reference_no' => $this->airReferenceNo($air),
            'reference_id' => (string) $air->id,
            'notes' => 'Inventory item created from inspected AIR promotion.',
            'fund_source_id' => $fundSourceId,
        ]);

        $copiedComponents = count(
            $this->components->copyAirUnitComponentsToInventory($unit, $inventoryItem)
        );

        return [
            'inventory_item' => $inventoryItem,
            'components_copied' => $copiedComponents,
        ];
    }

    /**
     * @return array{0:int,1:int}
     */
    private function promoteConsumables(
        string $actorUserId,
        ?string $actorName,
        Air $air,
        string $fundSourceId,
    ): array {
        $posted = 0;
        $skipped = 0;
        $occurredAt = ($air->date_inspected ?? $air->air_date ?? now())->copy()->endOfDay();
        $createdByName = $this->nullableString($actorName) ?? $actorUserId;

        foreach ($this->eligibleConsumables((string) $air->id) as $airItem) {
            $item = $airItem->item;

            if (! $item) {
                $skipped++;
                continue;
            }

            $qtyAccepted = max(0, (int) ($airItem->qty_accepted ?? 0));
            if ($qtyAccepted <= 0) {
                $skipped++;
                continue;
            }

            $conversion = $this->resolveBaseQuantity($item, $qtyAccepted, $airItem->unit_snapshot);
            $baseQty = (int) ($conversion['base_qty'] ?? 0);

            if ($baseQty <= 0) {
                $skipped++;
                continue;
            }

            $stock = $this->stocks->findByItemAndFundSource(
                itemId: (string) $item->id,
                fundSourceId: $fundSourceId,
                lockForUpdate: true,
                withTrashed: true,
            );

            if (! $stock) {
                $stock = $this->stocks->create([
                    'item_id' => (string) $item->id,
                    'fund_source_id' => $fundSourceId,
                    'on_hand' => 0,
                ]);
            } elseif ($stock->trashed()) {
                $this->stocks->restore($stock);
                $stock = $this->stocks->findByItemAndFundSource(
                    itemId: (string) $item->id,
                    fundSourceId: $fundSourceId,
                    lockForUpdate: true,
                ) ?? $stock;
            }

            $stock->on_hand = max(0, (int) ($stock->on_hand ?? 0)) + $baseQty;
            $this->stocks->save($stock);

            $remarks = 'Posted from inspected AIR.';
            if ((int) ($conversion['multiplier'] ?? 1) !== 1 && $this->nullableString($airItem->unit_snapshot) !== null) {
                $remarks .= sprintf(
                    ' (%d %s => %d %s)',
                    $qtyAccepted,
                    (string) $airItem->unit_snapshot,
                    $baseQty,
                    (string) ($conversion['base_unit'] ?? $airItem->unit_snapshot ?? 'base'),
                );
            }

            $this->stockMovements->create([
                'item_id' => (string) $item->id,
                'fund_source_id' => $fundSourceId,
                'movement_type' => StockMovementTypes::IN,
                'qty' => $baseQty,
                'reference_type' => 'AIR',
                'reference_id' => (string) $air->id,
                'air_item_id' => (string) $airItem->id,
                'ris_item_id' => null,
                'occurred_at' => $occurredAt,
                'created_by_name' => $createdByName,
                'remarks' => $remarks,
            ]);

            $posted++;
        }

        return [$posted, $skipped];
    }

    private function findPromotableAir(string $airId): Air
    {
        $air = Air::query()
            ->withTrashed()
            ->with([
                'department' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name', 'is_active']),
                'fundSource' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name', 'fund_cluster_id', 'is_active']),
            ])
            ->findOrFail($airId);

        if ($air->trashed()) {
            throw ValidationException::withMessages([
                'air' => ['Archived AIR records cannot be promoted into inventory.'],
            ]);
        }

        if ((string) ($air->status ?? '') !== AirStatuses::INSPECTED) {
            throw ValidationException::withMessages([
                'status' => ['Only inspected AIR records can be promoted into inventory.'],
            ]);
        }

        return $air;
    }

    /**
     * @return Collection<int, AirItemUnit>
     */
    private function eligiblePropertyUnits(string $airId): Collection
    {
        return $this->candidatePropertyUnits($airId)
            ->reject(fn (AirItemUnit $unit): bool => $this->resolvePropertyPromotionBlockReason($unit) !== null)
            ->values();
    }

    /**
     * @return Collection<int, AirItemUnit>
     */
    private function candidatePropertyUnits(string $airId): Collection
    {
        return AirItemUnit::query()
            ->with([
                'airItem' => fn ($query) => $query
                    ->with([
                        'air' => fn ($airQuery) => $airQuery
                            ->withTrashed()
                            ->select(['id', 'po_number', 'air_number', 'air_date', 'date_received']),
                        'item' => fn ($itemQuery) => $itemQuery
                            ->withTrashed()
                            ->with([
                                'asset' => fn ($assetQuery) => $assetQuery
                                    ->withTrashed()
                                    ->select(['id', 'asset_code', 'asset_name']),
                                'unitConversions' => fn ($conversionQuery) => $conversionQuery
                                    ->select(['id', 'item_id', 'from_unit', 'multiplier']),
                            ])
                            ->select([
                                'id',
                                'asset_id',
                                'item_name',
                                'description',
                                'base_unit',
                                'item_identification',
                                'tracking_type',
                                'requires_serial',
                                'is_semi_expendable',
                            ]),
                    ])
                    ->select([
                        'id',
                        'air_id',
                        'item_id',
                        'stock_no_snapshot',
                        'item_name_snapshot',
                        'description_snapshot',
                        'unit_snapshot',
                        'acquisition_cost',
                        'qty_accepted',
                        'tracking_type_snapshot',
                        'requires_serial_snapshot',
                        'is_semi_expendable_snapshot',
                    ]),
                'components',
                'files',
                'inventoryRecord' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'air_item_unit_id', 'deleted_at']),
            ])
            ->whereHas('airItem', fn ($query) => $query->where('air_id', $airId))
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (AirItemUnit $unit): bool => $unit->airItem instanceof AirItem)
            ->filter(fn (AirItemUnit $unit): bool => $this->shouldPromoteAsPropertyUnit($unit->airItem))
            ->reject(fn (AirItemUnit $unit): bool => $this->unitAlreadyPromoted($unit))
            ->values();
    }

    /**
     * @return Collection<int, AirItem>
     */
    private function eligibleConsumables(string $airId): Collection
    {
        return AirItem::query()
            ->with([
                'item' => fn ($query) => $query
                    ->withTrashed()
                    ->with([
                        'asset' => fn ($assetQuery) => $assetQuery
                            ->withTrashed()
                            ->select(['id', 'asset_code', 'asset_name']),
                        'unitConversions' => fn ($conversionQuery) => $conversionQuery
                            ->select(['id', 'item_id', 'from_unit', 'multiplier']),
                    ])
                    ->select([
                        'id',
                        'asset_id',
                        'item_name',
                        'description',
                        'base_unit',
                        'item_identification',
                        'tracking_type',
                        'requires_serial',
                        'is_semi_expendable',
                    ]),
            ])
            ->where('air_id', $airId)
            ->where('qty_accepted', '>', 0)
            ->orderBy('item_name_snapshot')
            ->orderBy('created_at')
            ->get()
            ->filter(fn (AirItem $airItem): bool => $airItem->item instanceof Item)
            ->filter(fn (AirItem $airItem): bool => $this->shouldPromoteAsConsumable($airItem))
            ->reject(fn (AirItem $airItem): bool => $this->hasAirStockMovement($airItem))
            ->values();
    }

    private function shouldPromoteAsPropertyUnit(AirItem $airItem): bool
    {
        $trackingType = strtolower(trim((string) ($airItem->tracking_type_snapshot ?? $airItem->item?->tracking_type ?? '')));

        return $trackingType !== 'consumable'
            || (bool) ($airItem->requires_serial_snapshot ?? false)
            || (bool) ($airItem->is_semi_expendable_snapshot ?? false);
    }

    private function shouldPromoteAsConsumable(AirItem $airItem): bool
    {
        $trackingType = strtolower(trim((string) ($airItem->tracking_type_snapshot ?? $airItem->item?->tracking_type ?? '')));

        return $trackingType === 'consumable'
            && ! (bool) ($airItem->requires_serial_snapshot ?? false)
            && ! (bool) ($airItem->is_semi_expendable_snapshot ?? false);
    }

    private function hasAirStockMovement(AirItem $airItem): bool
    {
        return StockMovement::query()
            ->withTrashed()
            ->where('reference_type', 'AIR')
            ->where('reference_id', (string) $airItem->air_id)
            ->where('air_item_id', (string) $airItem->id)
            ->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePropertyEligibility(AirItemUnit $unit): array
    {
        $airItem = $unit->airItem;
        $item = $airItem?->item;
        $isIcs = $airItem instanceof AirItem ? $this->isIcsClassification($airItem) : false;
        $components = $unit->relationLoaded('components')
            ? $unit->components
            : $unit->components()->get();
        $serializedComponents = $this->components->serializeComponents($components);
        $summary = $this->components->summarize($serializedComponents);

        return [
            'air_item_unit_id' => (string) $unit->id,
            'air_item_id' => (string) ($airItem?->id ?? ''),
            'item_id' => (string) ($item?->id ?? ''),
            'item_label' => $airItem instanceof AirItem ? $this->airItemLabel($airItem) : 'AIR Item',
            'unit_label' => $this->unitLabel($unit),
            'stock_no' => $this->nullableString($airItem?->stock_no_snapshot),
            'serial_number' => $this->nullableString($unit->serial_number),
            'property_number' => $this->nullableString($unit->property_number),
            'condition_status' => $this->nullableString($unit->condition_status),
            'condition_status_text' => InventoryConditions::labels()[(string) ($unit->condition_status ?? '')] ?? 'Unknown',
            'file_count' => $unit->relationLoaded('files') ? $unit->files->count() : 0,
            'classification' => $isIcs ? 'ICS' : 'PPE',
            'is_ics' => $isIcs,
            'acquisition_cost' => $airItem?->acquisition_cost !== null ? (float) $airItem->acquisition_cost : null,
            'components' => $serializedComponents,
            'component_count' => count($serializedComponents),
            'has_components' => (bool) ($summary['has_components'] ?? false),
            'component_total_cost' => (float) ($summary['component_total_cost'] ?? 0),
            'components_complete' => (bool) ($summary['components_complete'] ?? false),
            'promotion_blocked_reason' => $this->resolvePropertyPromotionBlockReason($unit),
            'promotion_warning' => $item instanceof Item
                ? $this->components->getComponentCostWarning(
                    rows: $serializedComponents,
                    parentUnitCost: $airItem?->acquisition_cost,
                    contextLabel: 'Component schedule',
                )
                : null,
        ];
    }

    private function resolvePropertyPromotionBlockReason(AirItemUnit $unit): ?string
    {
        $airItem = $unit->airItem;
        $item = $airItem?->item;

        if (! $airItem || ! $item) {
            return 'Linked item record was not found.';
        }

        return $this->components->getPromotionBlockReason(
            unit: $unit,
            item: $item,
            parentUnitCost: $airItem->acquisition_cost,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeConsumableEligibility(AirItem $airItem): array
    {
        $item = $airItem->item;
        $qtyAccepted = max(0, (int) ($airItem->qty_accepted ?? 0));
        $conversion = $item instanceof Item
            ? $this->resolveBaseQuantity($item, $qtyAccepted, $airItem->unit_snapshot)
            : ['base_qty' => $qtyAccepted, 'multiplier' => 1, 'base_unit' => $airItem->unit_snapshot];

        return [
            'air_item_id' => (string) $airItem->id,
            'item_id' => (string) ($item?->id ?? ''),
            'item_label' => $this->airItemLabel($airItem),
            'stock_no' => $this->nullableString($airItem->stock_no_snapshot),
            'qty_accepted' => $qtyAccepted,
            'unit_snapshot' => $this->nullableString($airItem->unit_snapshot),
            'base_qty' => (int) ($conversion['base_qty'] ?? 0),
            'base_unit' => $this->nullableString($conversion['base_unit'] ?? null),
            'multiplier' => (int) ($conversion['multiplier'] ?? 1),
        ];
    }

    private function resolvePoolDepartmentId(Air $air): string
    {
        $configuredDepartmentId = $this->nullableString(config('gso.pool.department_id'));
        if ($configuredDepartmentId !== null) {
            $department = Department::query()->find($configuredDepartmentId);

            if ($department && ! $department->trashed() && (bool) ($department->is_active ?? true)) {
                return (string) $department->id;
            }
        }

        $configuredDepartmentCode = $this->nullableString(config('gso.pool.department_code'));
        if ($configuredDepartmentCode !== null) {
            $department = Department::query()
                ->where('is_active', true)
                ->whereRaw('LOWER(code) = ?', [Str::lower($configuredDepartmentCode)])
                ->first();

            if ($department) {
                return (string) $department->id;
            }
        }

        $requestingDepartmentId = $this->nullableString($air->requesting_department_id);
        if ($requestingDepartmentId !== null) {
            $department = Department::query()->find($requestingDepartmentId);

            if ($department && ! $department->trashed()) {
                return (string) $department->id;
            }
        }

        throw ValidationException::withMessages([
            'department_id' => ['A valid GSO pool department is required before promoting AIR records.'],
        ]);
    }

    /**
     * @return array{0:?string,1:?string}
     */
    private function resolvePoolOfficer(Air $air, string $departmentId): array
    {
        $candidates = collect([
            $this->nullableString(config('gso.pool.accountable_officer_name')),
            $this->nullableString($air->accepted_by_name),
            $this->nullableString($air->inspected_by_name),
            'GSO Pool',
        ])
            ->filter()
            ->unique()
            ->values();

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeOfficerName($candidate);

            $officer = AccountableOfficer::query()
                ->where('department_id', $departmentId)
                ->where('is_active', true)
                ->where(function ($query) use ($candidate, $normalized) {
                    $query->whereRaw('LOWER(full_name) = ?', [Str::lower($candidate)]);

                    if ($normalized !== null) {
                        $query->orWhere('normalized_name', $normalized);
                    }
                })
                ->orderBy('full_name')
                ->first();

            if ($officer) {
                return [
                    (string) $officer->id,
                    $this->nullableString($officer->full_name),
                ];
            }
        }

        $fallbackName = $candidates->first();

        return [null, $fallbackName !== null ? (string) $fallbackName : null];
    }

    private function resolveFundSourceId(Air $air): string
    {
        $fundSourceId = $this->nullableString($air->fund_source_id);

        if ($fundSourceId === null) {
            throw ValidationException::withMessages([
                'fund_source_id' => ['A valid fund source is required before promoting AIR records.'],
            ]);
        }

        $fundSource = FundSource::query()->withTrashed()->find($fundSourceId);

        if (! $fundSource || $fundSource->trashed() || ! (bool) ($fundSource->is_active ?? true)) {
            throw ValidationException::withMessages([
                'fund_source_id' => ['The AIR fund source is no longer active or available.'],
            ]);
        }

        return (string) $fundSource->id;
    }

    private function resolveAcquisitionDate(Air $air): string
    {
        if ($air->date_received !== null) {
            return Carbon::parse($air->date_received)->toDateString();
        }

        if ($air->air_date !== null) {
            return Carbon::parse($air->air_date)->toDateString();
        }

        return now()->toDateString();
    }

    private function copyUnitFilesToInventoryItem(AirItemUnit $unit, InventoryItem $inventoryItem): int
    {
        $files = $unit->relationLoaded('files')
            ? $unit->files
            : $unit->files()->get();

        if ($files->isEmpty()) {
            return 0;
        }

        try {
            $folderId = $this->ensureInventoryFolder(
                $inventoryItem,
                $this->nullableString($unit->airItem?->air?->po_number)
            );
        } catch (Throwable) {
            return 0;
        }

        $copiedCount = 0;

        foreach ($files as $file) {
            if ((string) ($file->driver ?? '') !== 'google' || trim((string) ($file->path ?? '')) === '') {
                continue;
            }

            try {
                $copied = $this->driveFiles->copyFile(
                    sourceFileId: (string) $file->path,
                    newName: $this->nullableString($file->original_name),
                    targetFolderId: $folderId,
                );
            } catch (Throwable) {
                continue;
            }

            $driveFileId = trim((string) ($copied['drive_file_id'] ?? ''));
            if ($driveFileId === '') {
                continue;
            }

            $this->inventoryFiles->create([
                'inventory_item_id' => (string) $inventoryItem->id,
                'driver' => 'google',
                'path' => $driveFileId,
                'type' => $this->resolveInventoryFileType($file, $copied['mime_type'] ?? null),
                'is_primary' => (bool) ($file->is_primary ?? false),
                'position' => $this->inventoryFiles->nextPositionForInventoryItem((string) $inventoryItem->id),
                'original_name' => $this->nullableString($file->original_name),
                'mime' => $this->nullableString($file->mime)
                    ?? $this->nullableString($copied['mime_type'] ?? null),
                'size' => $file->size ?? ($copied['size'] ?? null),
                'caption' => $this->nullableString($file->caption),
            ]);

            $copiedCount++;
        }

        return $copiedCount;
    }

    private function ensureInventoryFolder(InventoryItem $inventoryItem, ?string $fallbackName = null): string
    {
        $existingFolderId = trim((string) ($inventoryItem->drive_folder_id ?? ''));

        if ($existingFolderId !== '') {
            return $existingFolderId;
        }

        $baseFolderId = trim((string) config(
            'gso.storage.inventory_files_folder_id',
            config('services.google_drive.folder_id', '')
        ));

        if ($baseFolderId === '') {
            throw new \RuntimeException('GSO inventory files folder is not configured.');
        }

        $folderName = $this->nullableString($inventoryItem->po_number)
            ?? $fallbackName
            ?? $this->nullableString($inventoryItem->property_number);

        if ($folderName === null) {
            throw ValidationException::withMessages([
                'po_number' => ['PO number or property number is required before copying inventory files.'],
            ]);
        }

        $folder = $this->driveFolders->ensureFolder($folderName, $baseFolderId);
        $folderId = trim((string) ($folder['drive_folder_id'] ?? ''));

        if ($folderId === '') {
            throw new \RuntimeException('Failed to resolve the Google Drive folder for inventory files.');
        }

        $inventoryItem->drive_folder_id = $folderId;
        $inventoryItem->save();

        return $folderId;
    }

    private function resolveInventoryFileType(AirItemUnitFile $file, mixed $fallbackMime): string
    {
        $type = trim((string) ($file->type ?? ''));

        if (in_array($type, InventoryFileTypes::values(), true)) {
            return $type;
        }

        $mime = trim((string) ($file->mime ?? $fallbackMime ?? ''));

        if (str_starts_with($mime, 'image/')) {
            return InventoryFileTypes::PHOTO;
        }

        if ($mime === 'application/pdf') {
            return InventoryFileTypes::PDF;
        }

        return InventoryFileTypes::DOCUMENT;
    }

    private function unitAlreadyPromoted(AirItemUnit $unit): bool
    {
        if ($this->nullableString($unit->inventory_item_id) !== null) {
            return true;
        }

        if ($unit->relationLoaded('inventoryRecord') && $unit->inventoryRecord !== null) {
            return true;
        }

        return InventoryItem::query()
            ->withTrashed()
            ->where('air_item_unit_id', (string) $unit->id)
            ->exists();
    }

    private function isIcsClassification(AirItem $airItem): bool
    {
        if ((bool) ($airItem->is_semi_expendable_snapshot ?? false)) {
            return true;
        }

        $amount = $airItem->acquisition_cost !== null ? (float) $airItem->acquisition_cost : null;
        if ($amount === null) {
            return false;
        }

        $threshold = (float) config('gso.inventory.ics_unit_cost_threshold', 50000);

        return $amount <= $threshold;
    }

    private function resolvePropertyNumber(
        AirItemUnit $unit,
        Item $item,
        bool $isIcs,
        string $acquisitionDate,
    ): string {
        $existing = $this->nullableString($unit->property_number);
        if ($existing !== null) {
            return $existing;
        }

        $year = Carbon::parse($acquisitionDate)->format('Y');
        $assetCode = $this->resolveAssetCode($item);
        $classification = $isIcs ? 'ICS' : 'PPE';
        $prefix = "{$year}-{$assetCode}-{$classification}";
        $next = InventoryItem::query()
            ->withTrashed()
            ->where('property_number', 'like', $prefix . '-%')
            ->pluck('property_number')
            ->map(function (mixed $value) use ($prefix): int {
                $value = trim((string) $value);

                if (! str_starts_with($value, $prefix . '-')) {
                    return 0;
                }

                $suffix = Str::afterLast($value, '-');

                return ctype_digit($suffix) ? (int) $suffix : 0;
            })
            ->max();

        return sprintf('%s-%05d', $prefix, ((int) $next) + 1);
    }

    private function resolveAssetCode(Item $item): string
    {
        $asset = $item->relationLoaded('asset')
            ? $item->asset
            : ($item->asset_id ? AssetCategory::query()->withTrashed()->find($item->asset_id) : null);

        $candidate = $this->nullableString($asset?->asset_code)
            ?? $this->nullableString($item->item_identification)
            ?? $this->nullableString($item->item_name)
            ?? 'ITEM';

        $sanitized = Str::upper(preg_replace('/[^A-Za-z0-9]+/', '-', $candidate) ?? 'ITEM');
        $sanitized = trim($sanitized, '-');

        return $sanitized !== '' ? $sanitized : 'ITEM';
    }

    private function normalizeInventoryCondition(mixed $value): string
    {
        $normalized = Str::lower(str_replace(' ', '_', trim((string) ($value ?? ''))));

        return in_array($normalized, InventoryConditions::values(), true)
            ? $normalized
            : InventoryConditions::GOOD;
    }

    private function normalizeInventoryStatus(mixed $condition): string
    {
        return $this->normalizeInventoryCondition($condition) === InventoryConditions::DAMAGED
            ? InventoryStatuses::FOR_REPAIR
            : InventoryStatuses::SERVICEABLE;
    }

    private function airReferenceNo(Air $air): ?string
    {
        $poNumber = $this->nullableString($air->po_number);
        $airNumber = $this->nullableString($air->air_number);

        if ($poNumber !== null && $airNumber !== null) {
            return "{$poNumber} / {$airNumber}";
        }

        return $poNumber ?? $airNumber;
    }

    private function airLabel(Air $air): string
    {
        return $this->airReferenceNo($air) ?? 'AIR Record';
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
        $serial = $this->nullableString($unit->serial_number);
        $property = $this->nullableString($unit->property_number);
        $brand = $this->nullableString($unit->brand);
        $model = $this->nullableString($unit->model);

        if ($property !== null && $serial !== null) {
            return "{$property} / {$serial}";
        }

        return $serial
            ?? $property
            ?? ($brand && $model ? "{$brand} {$model}" : ($brand ?? $model ?? 'Inspection Unit'));
    }

    /**
     * @return array{input_unit:?string,base_unit:?string,multiplier:int,base_qty:int}
     */
    private function resolveBaseQuantity(Item $item, int $quantity, ?string $unit): array
    {
        $quantity = max(0, $quantity);
        $inputUnit = $item->canonicalUnitValue($unit)
            ?? $this->nullableString($unit)
            ?? $this->nullableString($item->base_unit);
        $baseUnit = $this->nullableString($item->base_unit) ?? $inputUnit;
        $multiplier = 1;

        foreach ($item->getAvailableUnitOptions() as $option) {
            $value = trim((string) ($option['value'] ?? ''));

            if ($inputUnit !== null && Str::lower($value) === Str::lower($inputUnit)) {
                $multiplier = max(1, (int) ($option['multiplier'] ?? 1));
                break;
            }
        }

        return [
            'input_unit' => $inputUnit,
            'base_unit' => $baseUnit,
            'multiplier' => $multiplier,
            'base_qty' => $quantity * $multiplier,
        ];
    }

    private function normalizeOfficerName(?string $value): ?string
    {
        $value = $this->nullableString($value);

        if ($value === null) {
            return null;
        }

        $normalized = Str::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();

        return $normalized !== '' ? $normalized : null;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
