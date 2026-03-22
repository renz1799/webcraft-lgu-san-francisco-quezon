<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\StockDatatableRowBuilderInterface;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Models\Stock;
use App\Modules\GSO\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\StockRepositoryInterface;
use App\Modules\GSO\Services\Contracts\StockServiceInterface;
use App\Modules\GSO\Support\StockMovementTypes;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockService implements StockServiceInterface
{
    public function __construct(
        private readonly StockRepositoryInterface $stocks,
        private readonly StockMovementRepositoryInterface $movements,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly StockDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->stocks->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (Item $item) => $this->datatableRowBuilder->build($item))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function getLedgerViewData(string $itemId, array $filters): array
    {
        $item = $this->findConsumableItemOrFail($itemId);
        $page = max(1, (int) ($filters['page'] ?? 1));
        $size = max(1, min((int) ($filters['size'] ?? 15), 100));

        /** @var LengthAwarePaginator $movements */
        $movements = $this->movements->paginateForLedger($filters, $itemId, $page, $size);
        $stockRows = $this->stocks->activeRowsForItem($itemId);

        return [
            'item' => $item,
            'onHand' => (int) $stockRows->sum('on_hand'),
            'movements' => $movements,
            'availableFunds' => $this->buildAvailableFundOptions($stockRows),
            'filters' => [
                'date_from' => trim((string) ($filters['date_from'] ?? '')),
                'date_to' => trim((string) ($filters['date_to'] ?? '')),
                'type' => trim((string) ($filters['type'] ?? '')),
                'fund_source_id' => trim((string) ($filters['fund_source_id'] ?? '')),
            ],
        ];
    }

    public function getCardPrintViewData(string $itemId, ?string $fundSourceId, ?string $asOf = null): array
    {
        $item = $this->findConsumableItemOrFail($itemId);
        $stockRows = $this->stocks->activeRowsForItem($itemId);

        abort_if($stockRows->isEmpty(), 404, 'No stock records found for this item.');

        $selectedFundSourceId = $this->resolveCardFundSourceId($stockRows, $fundSourceId);
        $selectedStock = $stockRows->first(function (Stock $stock) use ($selectedFundSourceId): bool {
            return $this->normalizeNullableId($stock->fund_source_id) === $this->normalizeNullableId($selectedFundSourceId);
        });

        abort_if(! $selectedStock, 404, 'The selected stock row could not be found.');

        $asOfDate = trim((string) ($asOf ?? ''));
        $asOfEnd = $asOfDate !== ''
            ? Carbon::parse($asOfDate)->endOfDay()
            : null;

        $movements = $this->movements->getForCard(
            itemId: $itemId,
            fundSourceId: $selectedFundSourceId,
            includeNullFundSource: $stockRows->count() === 1 && $selectedFundSourceId !== null,
            asOf: $asOfEnd,
        );

        $runningBalance = 0;
        $rows = [];

        foreach ($movements as $movement) {
            [$receiptQty, $issueQty, $runningBalance] = $this->resolveCardMovementQuantities(
                (string) ($movement->movement_type ?? ''),
                (int) ($movement->qty ?? 0),
                $runningBalance,
            );

            $rows[] = [
                'date' => $movement->occurred_at?->format('Y-m-d') ?? '',
                'reference' => $this->buildMovementReferenceLabel($movement),
                'receipt_qty' => $receiptQty,
                'issue_qty' => $issueQty,
                'balance_qty' => $runningBalance,
                'remarks' => trim((string) ($movement->remarks ?? '')),
                'fund_source' => $this->buildFundSourceLabel(
                    $movement->fundSource?->code,
                    $movement->fundSource?->name,
                ),
            ];
        }

        return [
            'card' => [
                'entity_name' => config('print.entity_name')
                    ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
                'fund_cluster' => $this->buildFundClusterLabel(
                    $selectedStock->fundSource?->fundCluster?->code,
                    $selectedStock->fundSource?->fundCluster?->name,
                ),
                'fund_source' => $this->buildFundSourceLabel(
                    $selectedStock->fundSource?->code,
                    $selectedStock->fundSource?->name,
                ) ?: 'Unassigned Stock Row',
                'item_name' => (string) ($item->item_name ?? ''),
                'stock_no' => trim((string) ($item->item_identification ?? '')),
                'description' => trim((string) ($item->description ?? '')),
                'unit' => trim((string) ($item->base_unit ?? '')),
                'as_of' => $asOfDate,
                'fund_source_id' => $selectedFundSourceId ?? '',
                'current_on_hand' => (int) ($selectedStock->on_hand ?? 0),
            ],
            'rows' => $rows,
            'available_funds' => $this->buildAvailableFundOptions($stockRows),
        ];
    }

    public function adjustManual(
        string $actorUserId,
        string $actorName,
        string $itemId,
        string $type,
        int $qty,
        ?string $fundSourceId = null,
        ?string $remarks = null
    ): array {
        $type = trim(strtolower($type));
        if (! in_array($type, ['increase', 'decrease', 'set'], true)) {
            throw ValidationException::withMessages([
                'type' => ['Invalid adjustment type.'],
            ]);
        }

        if ($qty <= 0) {
            throw ValidationException::withMessages([
                'qty' => ['Quantity must be greater than 0.'],
            ]);
        }

        return DB::transaction(function () use ($actorUserId, $actorName, $itemId, $type, $qty, $fundSourceId, $remarks) {
            $item = $this->findConsumableItemOrFail($itemId);
            $activeRows = $this->stocks->activeRowsForItem($itemId);
            $resolvedFundSourceId = $this->resolveAdjustmentFundSourceId($activeRows, $fundSourceId);

            if ($resolvedFundSourceId !== null) {
                $fundSource = FundSource::query()->find($resolvedFundSourceId);
                if (! $fundSource || $fundSource->trashed() || ! $fundSource->is_active) {
                    throw ValidationException::withMessages([
                        'fund_source_id' => ['Selected fund source is invalid.'],
                    ]);
                }
            }

            $stockRow = $this->stocks->findByItemAndFundSource(
                itemId: $itemId,
                fundSourceId: $resolvedFundSourceId,
                lockForUpdate: true,
                withTrashed: true,
            );

            if (! $stockRow) {
                $stockRow = $this->stocks->create([
                    'item_id' => $itemId,
                    'fund_source_id' => $resolvedFundSourceId,
                    'on_hand' => 0,
                ]);
            } elseif ($stockRow->trashed()) {
                $this->stocks->restore($stockRow);
            }

            $oldOnHand = (int) ($stockRow->on_hand ?? 0);
            [$movementType, $movementQty, $newOnHand] = $this->resolveAdjustmentOutcome($type, $qty, $oldOnHand);

            $stockRow->fill([
                'fund_source_id' => $resolvedFundSourceId,
                'on_hand' => $newOnHand,
            ]);
            $stockRow = $this->stocks->save($stockRow);

            $storedRemarks = trim((string) $remarks);
            $storedRemarks = $storedRemarks !== '' ? $storedRemarks : 'Manual stock adjustment';

            $movement = $this->movements->create([
                'id' => (string) Str::uuid(),
                'item_id' => $itemId,
                'fund_source_id' => $resolvedFundSourceId,
                'movement_type' => $movementType,
                'qty' => $movementQty,
                'reference_type' => 'MANUAL_ADJUST',
                'reference_id' => null,
                'air_item_id' => null,
                'ris_item_id' => null,
                'occurred_at' => now(),
                'created_by_name' => $actorName,
                'remarks' => $storedRemarks,
            ]);

            $this->auditLogs->record(
                action: 'gso.stock.adjusted',
                subject: $item,
                changesOld: [
                    'on_hand' => $oldOnHand,
                    'fund_source_id' => $resolvedFundSourceId,
                ],
                changesNew: [
                    'on_hand' => $newOnHand,
                    'fund_source_id' => $resolvedFundSourceId,
                    'item_id' => $itemId,
                    'type' => $type,
                    'qty' => $qty,
                ],
                meta: [
                    'actor_user_id' => $actorUserId,
                    'created_by_name' => $actorName,
                    'movement_id' => (string) $movement->id,
                    'remarks' => $storedRemarks,
                ],
                message: sprintf(
                    'GSO stock adjusted: %s | %s qty %d | On-hand %d -> %d',
                    $this->itemLabel($item),
                    $this->adjustmentTypeLabel($type),
                    $qty,
                    $oldOnHand,
                    $newOnHand,
                ),
                display: $this->buildStockAdjustedDisplay(
                    $item,
                    $type,
                    $qty,
                    $oldOnHand,
                    $newOnHand,
                    $storedRemarks,
                    $stockRow->fundSource ? $this->buildFundSourceLabel(
                        $stockRow->fundSource->code,
                        $stockRow->fundSource->name,
                    ) : 'Unassigned Stock Row',
                ),
            );

            return [
                'movement_id' => (string) $movement->id,
                'old_on_hand' => $oldOnHand,
                'new_on_hand' => $newOnHand,
                'fund_source_id' => $resolvedFundSourceId,
                'message' => sprintf(
                    '%s stock adjusted for %s.',
                    $this->adjustmentTypeLabel($type),
                    $this->itemLabel($item),
                ),
            ];
        });
    }

    private function findConsumableItemOrFail(string $itemId): Item
    {
        return Item::query()
            ->where('tracking_type', 'consumable')
            ->findOrFail($itemId);
    }

    /**
     * @param  Collection<int, Stock>  $stockRows
     * @return array<int, array{id: string, label: string, on_hand: int}>
     */
    private function buildAvailableFundOptions(Collection $stockRows): array
    {
        return $stockRows
            ->map(function (Stock $stock): array {
                $fundSource = $stock->fundSource;

                return [
                    'id' => $fundSource?->id ? (string) $fundSource->id : '',
                    'label' => $fundSource
                        ? $this->buildFundSourceLabel($fundSource->code, $fundSource->name)
                        : 'Unassigned Stock Row',
                    'on_hand' => (int) ($stock->on_hand ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Stock>  $stockRows
     */
    private function resolveCardFundSourceId(Collection $stockRows, ?string $requestedFundSourceId): ?string
    {
        $requested = $this->normalizeNullableId($requestedFundSourceId);

        if ($requested !== null) {
            $match = $stockRows->first(function (Stock $stock) use ($requested): bool {
                return $this->normalizeNullableId($stock->fund_source_id) === $requested;
            });

            abort_if(! $match, 404, 'The selected fund source is not available for this stock card.');

            return $requested;
        }

        if ($stockRows->count() === 1) {
            return $this->normalizeNullableId((string) ($stockRows->first()?->fund_source_id ?? ''));
        }

        $preferred = $stockRows->first(function (Stock $stock): bool {
            return $this->normalizeNullableId($stock->fund_source_id) !== null;
        });

        return $preferred
            ? $this->normalizeNullableId((string) ($preferred->fund_source_id ?? ''))
            : null;
    }

    /**
     * @param  Collection<int, Stock>  $activeRows
     */
    private function resolveAdjustmentFundSourceId(Collection $activeRows, ?string $requestedFundSourceId): ?string
    {
        $requested = $this->normalizeNullableId($requestedFundSourceId);

        if ($requested !== null) {
            return $requested;
        }

        if ($activeRows->count() === 1) {
            return $this->normalizeNullableId((string) ($activeRows->first()?->fund_source_id ?? ''));
        }

        if ($activeRows->count() > 1) {
            throw ValidationException::withMessages([
                'fund_source_id' => ['Select a fund source when the item has multiple stock balances.'],
            ]);
        }

        return null;
    }

    /**
     * @return array{0:string,1:int,2:int}
     */
    private function resolveAdjustmentOutcome(string $type, int $qty, int $oldOnHand): array
    {
        return match ($type) {
            'increase' => [StockMovementTypes::ADJUST_IN, $qty, $oldOnHand + $qty],
            'decrease' => [StockMovementTypes::ADJUST_OUT, $qty, max(0, $oldOnHand - $qty)],
            default => [StockMovementTypes::ADJUST_SET, $qty, $qty],
        };
    }

    /**
     * @return array{0:int|null,1:int|null,2:int}
     */
    private function resolveCardMovementQuantities(string $movementType, int $quantity, int $runningBalance): array
    {
        return match (StockMovementTypes::normalize($movementType)) {
            StockMovementTypes::IN,
            StockMovementTypes::RESTORE,
            StockMovementTypes::ADJUST_IN => [$quantity > 0 ? $quantity : null, null, $runningBalance + $quantity],
            StockMovementTypes::ISSUE,
            StockMovementTypes::ADJUST_OUT => [null, $quantity > 0 ? $quantity : null, max(0, $runningBalance - $quantity)],
            StockMovementTypes::ADJUST_SET => $this->resolveSetAdjustmentQuantities($quantity, $runningBalance),
            default => [null, null, $runningBalance],
        };
    }

    /**
     * @return array{0:int|null,1:int|null,2:int}
     */
    private function resolveSetAdjustmentQuantities(int $targetBalance, int $runningBalance): array
    {
        if ($targetBalance > $runningBalance) {
            return [$targetBalance - $runningBalance, null, $targetBalance];
        }

        if ($targetBalance < $runningBalance) {
            return [null, $runningBalance - $targetBalance, $targetBalance];
        }

        return [null, null, $targetBalance];
    }

    private function buildMovementReferenceLabel(object $movement): string
    {
        $referenceType = trim((string) ($movement->reference_type ?? ''));
        $referenceId = trim((string) ($movement->reference_id ?? ''));

        if ($referenceType === '' && $referenceId === '') {
            return 'Manual Adjustment';
        }

        if ($referenceType !== '' && $referenceId !== '') {
            return sprintf('%s: %s', $referenceType, $referenceId);
        }

        return $referenceType !== '' ? $referenceType : $referenceId;
    }

    private function buildFundClusterLabel(?string $code, ?string $name): string
    {
        $code = trim((string) $code);
        $name = trim((string) $name);

        if ($code !== '' && $name !== '') {
            return sprintf('%s - %s', $code, $name);
        }

        return $code !== '' ? $code : $name;
    }

    private function buildFundSourceLabel(?string $code, ?string $name): string
    {
        $code = trim((string) $code);
        $name = trim((string) $name);

        if ($code !== '' && $name !== '') {
            return sprintf('%s - %s', $code, $name);
        }

        return $code !== '' ? $code : $name;
    }

    private function adjustmentTypeLabel(string $type): string
    {
        return match (trim(strtolower($type))) {
            'increase' => 'Increase',
            'decrease' => 'Decrease',
            'set' => 'Set Quantity',
            default => 'Adjustment',
        };
    }

    private function itemLabel(?Item $item): string
    {
        if (! $item) {
            return 'Item';
        }

        $name = trim((string) ($item->item_name ?? ''));
        $identification = trim((string) ($item->item_identification ?? ''));

        if ($name !== '' && $identification !== '') {
            return sprintf('%s (%s)', $name, $identification);
        }

        return $name !== '' ? $name : ($identification !== '' ? $identification : 'Item');
    }

    private function trackingTypeLabel(?string $value): string
    {
        return match (trim(strtolower((string) $value))) {
            'consumable' => 'Consumable',
            'property' => 'Property',
            default => 'None',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStockAdjustedDisplay(
        ?Item $item,
        string $type,
        int $qty,
        int $oldOnHand,
        int $newOnHand,
        string $remarks,
        string $fundSourceLabel
    ): array {
        return [
            'summary' => sprintf(
                '%s stock adjusted for %s',
                $this->adjustmentTypeLabel($type),
                $this->itemLabel($item),
            ),
            'sections' => [
                [
                    'title' => 'Stock Adjustment',
                    'items' => [
                        [
                            'label' => 'Adjustment Type',
                            'before' => null,
                            'after' => $this->adjustmentTypeLabel($type),
                        ],
                        [
                            'label' => 'Quantity',
                            'before' => null,
                            'after' => (string) $qty,
                        ],
                        [
                            'label' => 'On-Hand Quantity',
                            'before' => (string) $oldOnHand,
                            'after' => (string) $newOnHand,
                        ],
                        [
                            'label' => 'Fund Source',
                            'before' => null,
                            'after' => $fundSourceLabel,
                        ],
                        [
                            'label' => 'Remarks',
                            'before' => null,
                            'after' => $remarks !== '' ? $remarks : 'Manual stock adjustment',
                        ],
                    ],
                ],
                [
                    'title' => 'Item Details',
                    'items' => [
                        [
                            'label' => 'Item',
                            'value' => $this->itemLabel($item),
                        ],
                        [
                            'label' => 'Tracking Type',
                            'value' => $this->trackingTypeLabel($item?->tracking_type),
                        ],
                        [
                            'label' => 'Base Unit',
                            'value' => trim((string) ($item?->base_unit ?? '')) !== ''
                                ? (string) $item?->base_unit
                                : 'None',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function normalizeNullableId(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
