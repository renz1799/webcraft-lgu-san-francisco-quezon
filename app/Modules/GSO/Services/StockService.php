<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Builders\Contracts\StockDatatableRowBuilderInterface;
use App\Modules\GSO\Models\AccountableOfficer;
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
    private const PAPER_OVERRIDE_KEYS = [
        'rows_per_page',
        'grid_rows',
        'last_page_grid_rows',
        'description_chars_per_line',
    ];

    public function __construct(
        private readonly StockRepositoryInterface $stocks,
        private readonly StockMovementRepositoryInterface $movements,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly StockDatatableRowBuilderInterface $datatableRowBuilder,
        private readonly PdfGeneratorInterface $pdfGenerator,
        private readonly PrintConfigLoaderInterface $printConfigLoader,
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

    public function getRpciPrintViewData(
        ?string $fundSourceId,
        ?string $asOf = null,
        ?string $inventoryType = null,
        bool $prefillCount = false,
        ?string $accountableOfficerId = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = [],
    ): array {
        $activeFundSources = FundSource::query()
            ->where('is_active', true)
            ->with('fundCluster')
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        abort_if($activeFundSources->isEmpty(), 404, 'No active fund sources are available for RPCI printing.');

        $selectedFundSourceId = $this->resolveRpciFundSourceId($activeFundSources, $fundSourceId);
        $selectedFund = $activeFundSources->firstWhere('id', $selectedFundSourceId);

        abort_if(! $selectedFund, 404, 'The selected fund source was not found.');

        $selectedOfficer = $this->resolveRpciSelectedOfficer($accountableOfficerId);
        $asOfDate = $this->resolveRpciAsOfDate($asOf);
        [$rows, $summary] = $this->buildRpciRows(
            fundSourceId: $selectedFundSourceId,
            asOfDate: $asOfDate,
            prefillCount: $prefillCount,
        );
        $paperProfile = $this->resolveRpciPaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildRpciPagination($rows, $paperProfile);

        $inventoryType = trim((string) ($inventoryType ?? ''));
        $resolvedSummary = $summary + [
            'printed_rows' => count($rows),
        ];

        return [
            'report' => [
                'title' => 'Report on the Physical Count of Inventories',
                'document' => [
                    'entity_name' => config('print.entity_name')
                        ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
                    'appendix_label' => 'Annex 48',
                    'fund_source_id' => $selectedFundSourceId,
                    'accountable_officer_id' => $selectedOfficer?->id ? (string) $selectedOfficer->id : '',
                    'fund_source' => $this->buildFundSourceLabel(
                        $selectedFund->code,
                        $selectedFund->name,
                    ),
                    'fund_cluster' => $this->buildFundClusterLabel(
                        $selectedFund->fundCluster?->code,
                        $selectedFund->fundCluster?->name,
                    ),
                    'inventory_type' => $inventoryType !== '' ? $inventoryType : 'Office Supplies',
                    'as_of' => $asOfDate->toDateString(),
                    'as_of_label' => $asOfDate->format('F j, Y'),
                    'prefill_count' => $prefillCount,
                    'summary' => $resolvedSummary,
                    'signatories' => $this->buildRpciSignatories($signatories, $selectedOfficer),
                ],
                'rows' => $rows,
                'pagination' => $pagination,
            ],
            'paperProfile' => $paperProfile,
            'available_funds' => $activeFundSources
                ->map(fn (FundSource $fund): array => [
                    'id' => (string) $fund->id,
                    'label' => $this->buildFundSourceLabel($fund->code, $fund->name),
                ])
                ->values()
                ->all(),
        ];
    }

    public function generateRpciPdf(
        ?string $fundSourceId,
        ?string $asOf = null,
        ?string $inventoryType = null,
        bool $prefillCount = false,
        ?string $accountableOfficerId = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = [],
    ): string {
        $payload = $this->getRpciPrintViewData(
            fundSourceId: $fundSourceId,
            asOf: $asOf,
            inventoryType: $inventoryType,
            prefillCount: $prefillCount,
            accountableOfficerId: $accountableOfficerId,
            signatories: $signatories,
            requestedPaper: $requestedPaper,
            paperOverrides: $paperOverrides,
        );

        $filename = 'rpci-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::reports.rpci.print.pdf',
            data: [
                'report' => $payload['report'],
                'paperProfile' => $payload['paperProfile'],
            ],
            outputPath: $outputPath,
        );
    }

    public function getSsmiPrintViewData(
        ?string $fundSourceId,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = [],
    ): array {
        $activeFundSources = FundSource::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->with('fundCluster')
            ->orderBy('code')
            ->orderBy('name')
            ->get();

        abort_if($activeFundSources->isEmpty(), 404, 'No active fund sources are available for SSMI printing.');

        $selectedFundSourceId = $this->resolveSsmiFundSourceId($activeFundSources, $fundSourceId);
        $selectedFund = $activeFundSources->firstWhere('id', $selectedFundSourceId);

        abort_if(! $selectedFund, 404, 'The selected fund source was not found.');

        [$periodFrom, $periodTo] = $this->resolveSsmiPeriod($dateFrom, $dateTo);
        [$rows, $summary] = $this->buildSsmiRows(
            fundSourceId: $selectedFundSourceId,
            periodFrom: $periodFrom,
            periodTo: $periodTo,
        );

        $document = [
            'entity_name' => config('print.entity_name')
                ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
            'appendix_label' => 'SSMI',
            'fund_source_id' => $selectedFundSourceId,
            'fund_source' => $this->buildFundSourceLabel(
                $selectedFund->code,
                $selectedFund->name,
            ),
            'fund_cluster' => $this->buildFundClusterLabel(
                $selectedFund->fundCluster?->code,
                $selectedFund->fundCluster?->name,
            ),
            'period_from' => $periodFrom->toDateString(),
            'period_to' => $periodTo->toDateString(),
            'period_label' => sprintf('%s to %s', $periodFrom->format('F j, Y'), $periodTo->format('F j, Y')),
            'summary' => $summary,
            'signatories' => $this->buildSsmiSignatories($signatories),
        ];

        $paperProfile = $this->resolveSsmiPaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildSsmiPagination($rows, $paperProfile);

        return [
            'report' => [
                'title' => 'Summary of Supplies and Materials Issued',
                'document' => $document,
                'rows' => $rows,
                'pagination' => $pagination,
            ],
            'paperProfile' => $paperProfile,
            'available_funds' => $activeFundSources
                ->map(fn (FundSource $fund): array => [
                    'id' => (string) $fund->id,
                    'label' => $this->buildFundSourceLabel($fund->code, $fund->name),
                ])
                ->values()
                ->all(),
        ];
    }

    public function generateSsmiPdf(
        ?string $fundSourceId,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = [],
    ): string {
        $payload = $this->getSsmiPrintViewData(
            fundSourceId: $fundSourceId,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            signatories: $signatories,
            requestedPaper: $requestedPaper,
            paperOverrides: $paperOverrides,
        );

        $filename = 'ssmi-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::reports.ssmi.print.pdf',
            data: [
                'report' => $payload['report'],
                'paperProfile' => $payload['paperProfile'],
            ],
            outputPath: $outputPath,
        );
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
     * @param  Collection<int, FundSource>  $activeFundSources
     */
    private function resolveRpciFundSourceId(Collection $activeFundSources, ?string $requestedFundSourceId): string
    {
        $requested = $this->normalizeNullableId($requestedFundSourceId);

        if ($requested !== null) {
            $match = $activeFundSources->first(function (FundSource $fundSource) use ($requested): bool {
                return (string) $fundSource->id === $requested;
            });

            abort_if(! $match, 404, 'The selected fund source is not available for RPCI printing.');

            return $requested;
        }

        $generalFund = $activeFundSources->first(function (FundSource $fundSource): bool {
            $code = Str::lower(trim((string) ($fundSource->code ?? '')));
            $name = Str::lower(trim((string) ($fundSource->name ?? '')));

            return $code === 'general fund' || $name === 'general fund';
        });

        return (string) ($generalFund?->id ?? $activeFundSources->first()?->id ?? '');
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

    private function resolveRpciAsOfDate(?string $asOf): Carbon
    {
        $asOf = trim((string) ($asOf ?? ''));

        return $asOf !== ''
            ? Carbon::parse($asOf)->startOfDay()
            : Carbon::today()->startOfDay();
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

    /**
     * @return array{0:array<int, array<string, mixed>>, 1:array<string, int|float|null>}
     */
    private function buildRpciRows(string $fundSourceId, Carbon $asOfDate, bool $prefillCount): array
    {
        $stockRows = DB::table('stocks as s')
            ->join('items as i', function ($join) {
                $join->on('i.id', '=', 's.item_id')
                    ->whereNull('i.deleted_at');
            })
            ->whereNull('s.deleted_at')
            ->where('s.fund_source_id', $fundSourceId)
            ->where('i.tracking_type', 'consumable')
            ->orderBy('i.item_identification')
            ->orderBy('i.item_name')
            ->select([
                's.item_id',
                's.on_hand',
                'i.item_name',
                'i.item_identification',
                'i.description',
                'i.base_unit',
            ])
            ->get();

        if ($stockRows->isEmpty()) {
            return [[], [
                'total_items' => 0,
                'total_balance_qty' => 0,
                'total_count_qty' => $prefillCount ? 0 : null,
                'total_shortage_overage_qty' => $prefillCount ? 0 : null,
                'total_shortage_overage_value' => $prefillCount ? 0.0 : null,
                'total_book_value' => 0.0,
            ]];
        }

        $itemIds = $stockRows
            ->pluck('item_id')
            ->filter(fn ($itemId) => trim((string) $itemId) !== '')
            ->map(fn ($itemId) => (string) $itemId)
            ->unique()
            ->values()
            ->all();

        $movementRows = DB::table('stock_movements as sm')
            ->whereNull('sm.deleted_at')
            ->where('sm.fund_source_id', $fundSourceId)
            ->whereIn('sm.item_id', $itemIds)
            ->where('sm.occurred_at', '<=', $asOfDate->copy()->endOfDay())
            ->orderBy('sm.item_id')
            ->orderBy('sm.occurred_at')
            ->orderBy('sm.created_at')
            ->select([
                'sm.item_id',
                'sm.movement_type',
                'sm.qty',
            ])
            ->get();

        $balanceByItem = [];
        foreach ($movementRows as $movement) {
            $itemId = (string) ($movement->item_id ?? '');
            if ($itemId === '') {
                continue;
            }

            $runningBalance = (int) ($balanceByItem[$itemId] ?? 0);
            [, , $runningBalance] = $this->resolveCardMovementQuantities(
                (string) ($movement->movement_type ?? ''),
                (int) ($movement->qty ?? 0),
                $runningBalance,
            );

            $balanceByItem[$itemId] = $runningBalance;
        }

        $isToday = $asOfDate->isSameDay(Carbon::today());
        $unitValueMap = $this->buildRpciLatestUnitValueMap(
            fundSourceId: $fundSourceId,
            itemIds: $itemIds,
            asOfDate: $asOfDate,
        );

        $rows = [];
        $totalBalanceQty = 0;
        $totalCountQty = 0;
        $totalBookValue = 0.0;

        foreach ($stockRows as $stockRow) {
            $itemId = (string) ($stockRow->item_id ?? '');
            if ($itemId === '') {
                continue;
            }

            $balanceQty = $isToday
                ? (int) ($stockRow->on_hand ?? 0)
                : (int) ($balanceByItem[$itemId] ?? 0);

            if ($balanceQty <= 0) {
                continue;
            }

            $unitValue = round((float) ($unitValueMap[$itemId] ?? 0.0), 2);
            $countQty = $prefillCount ? $balanceQty : null;
            $shortageQty = $prefillCount ? 0 : null;
            $shortageValue = $prefillCount ? 0.0 : null;

            $rows[] = [
                'article' => trim((string) ($stockRow->item_name ?? '')) ?: 'Item',
                'description' => $this->buildRpciDescription($stockRow),
                'stock_no' => trim((string) ($stockRow->item_identification ?? '')),
                'unit' => trim((string) ($stockRow->base_unit ?? '')),
                'unit_value' => $unitValue,
                'balance_per_card_qty' => $balanceQty,
                'count_qty' => $countQty,
                'shortage_overage_qty' => $shortageQty,
                'shortage_overage_value' => $shortageValue,
                'remarks' => '',
            ];

            $totalBalanceQty += $balanceQty;
            $totalBookValue += ($balanceQty * $unitValue);
            if ($prefillCount) {
                $totalCountQty += $balanceQty;
            }
        }

        return [$rows, [
            'total_items' => count($rows),
            'total_balance_qty' => $totalBalanceQty,
            'total_count_qty' => $prefillCount ? $totalCountQty : null,
            'total_shortage_overage_qty' => $prefillCount ? 0 : null,
            'total_shortage_overage_value' => $prefillCount ? 0.0 : null,
            'total_book_value' => round($totalBookValue, 2),
        ]];
    }

    /**
     * @param  array<int, string>  $itemIds
     * @return array<string, float>
     */
    private function buildRpciLatestUnitValueMap(string $fundSourceId, array $itemIds, Carbon $asOfDate): array
    {
        if ($itemIds === []) {
            return [];
        }

        $receiptRows = DB::table('stock_movements as sm')
            ->join('air_items as ai', 'ai.id', '=', 'sm.air_item_id')
            ->leftJoin('items as i', function ($join) {
                $join->on('i.id', '=', 'sm.item_id')
                    ->whereNull('i.deleted_at');
            })
            ->leftJoin('item_unit_conversions as iuc', function ($join) {
                $join->on('iuc.item_id', '=', 'ai.item_id')
                    ->on('iuc.from_unit', '=', 'ai.unit_snapshot')
                    ->whereNull('iuc.deleted_at');
            })
            ->whereNull('sm.deleted_at')
            ->where('sm.movement_type', 'in')
            ->where('sm.fund_source_id', $fundSourceId)
            ->whereIn('sm.item_id', $itemIds)
            ->where('sm.occurred_at', '<=', $asOfDate->copy()->endOfDay())
            ->orderBy('sm.item_id')
            ->orderByDesc('sm.occurred_at')
            ->orderByDesc('sm.created_at')
            ->select([
                'sm.item_id',
                'ai.acquisition_cost',
                'ai.unit_snapshot',
                'i.base_unit',
                'iuc.multiplier',
            ])
            ->get();

        $unitValues = [];

        foreach ($receiptRows as $receipt) {
            $itemId = (string) ($receipt->item_id ?? '');
            if ($itemId === '' || array_key_exists($itemId, $unitValues)) {
                continue;
            }

            $baseUnit = trim((string) ($receipt->base_unit ?? ''));
            $receiptUnit = trim((string) ($receipt->unit_snapshot ?? ''));
            $multiplier = 1;

            if ($receiptUnit !== '' && $baseUnit !== '' && strcasecmp($receiptUnit, $baseUnit) !== 0) {
                $multiplier = max(1, (int) ($receipt->multiplier ?? 1));
            }

            $acquisitionCost = max(0.0, (float) ($receipt->acquisition_cost ?? 0.0));
            $unitValues[$itemId] = round(
                $multiplier > 0 ? ($acquisitionCost / $multiplier) : $acquisitionCost,
                2
            );
        }

        return $unitValues;
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

    private function buildRpciDescription(object $row): string
    {
        $description = trim((string) ($row->description ?? ''));
        $itemName = trim((string) ($row->item_name ?? ''));

        if ($description !== '' && $itemName !== '' && stripos($description, $itemName) === false) {
            return sprintf('%s - %s', $itemName, $description);
        }

        if ($description !== '') {
            return $description;
        }

        return $itemName !== '' ? $itemName : 'Inventory Item';
    }

    private function resolveRpciSelectedOfficer(?string $accountableOfficerId): ?AccountableOfficer
    {
        $accountableOfficerId = $this->normalizeNullableId($accountableOfficerId);

        if ($accountableOfficerId === null) {
            return null;
        }

        return AccountableOfficer::query()
            ->whereNull('deleted_at')
            ->find($accountableOfficerId);
    }

    /**
     * @param  array<string, mixed>  $paperOverrides
     * @return array<string, mixed>
     */
    private function resolveRpciPaperProfile(?string $requestedPaper, array $paperOverrides): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_rpci', $requestedPaper);

        foreach (self::PAPER_OVERRIDE_KEYS as $key) {
            if (! array_key_exists($key, $paperOverrides)) {
                continue;
            }

            $value = $paperOverrides[$key];
            if (! is_int($value)) {
                continue;
            }

            $paperProfile[$key] = $value;
        }

        return $paperProfile;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $paperProfile
     * @return array<string, mixed>
     */
    private function buildRpciPagination(array $rows, array $paperProfile): array
    {
        $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 16));
        $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? $rowsPerPage));
        $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
        $descriptionCharsPerLine = max(1, (int) ($paperProfile['description_chars_per_line'] ?? 44));
        $pages = [];
        $cursor = 0;

        if ($rows === []) {
            $pages[] = [
                'rows' => [],
                'used_units' => 0,
            ];
        } else {
            while ($cursor < count($rows)) {
                $pageRows = [];
                $usedUnits = 0;

                while ($cursor < count($rows)) {
                    $row = $rows[$cursor];
                    $rowUnits = $this->estimateRpciRowUnits($row, $descriptionCharsPerLine);

                    if ($pageRows !== [] && ($usedUnits + $rowUnits) > $rowsPerPage) {
                        break;
                    }

                    $pageRows[] = $row;
                    $usedUnits += $rowUnits;
                    $cursor++;

                    if ($usedUnits >= $rowsPerPage) {
                        break;
                    }
                }

                if ($pageRows === []) {
                    $row = $rows[$cursor];
                    $pageRows[] = $row;
                    $usedUnits = $this->estimateRpciRowUnits($row, $descriptionCharsPerLine);
                    $cursor++;
                }

                $pages[] = [
                    'rows' => $pageRows,
                    'used_units' => $usedUnits,
                ];
            }
        }

        $pageRowCounts = array_map(
            static fn (array $page): int => count($page['rows'] ?? []),
            $pages,
        );
        $pageUsedUnits = array_map(
            static fn (array $page): int => (int) ($page['used_units'] ?? 0),
            $pages,
        );
        $lastUsedUnits = (int) ($pageUsedUnits !== [] ? end($pageUsedUnits) : 0);

        return [
            'pages' => $pages,
            'stats' => [
                'page_count' => max(1, count($pages)),
                'rows_per_page' => $rowsPerPage,
                'grid_rows' => $gridRows,
                'last_page_grid_rows' => $lastPageGridRows,
                'description_chars_per_line' => $descriptionCharsPerLine,
                'page_row_counts' => $pageRowCounts,
                'page_used_units' => $pageUsedUnits,
                'last_page_padding' => $lastPageGridRows > 0
                    ? max(0, $lastPageGridRows - $lastUsedUnits)
                    : 0,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function estimateRpciRowUnits(array $row, int $descriptionCharsPerLine): int
    {
        $articleCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (14 / 22)));
        $remarksCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (10 / 22)));

        $descriptionUnits = $this->estimateRpciCellUnits((string) ($row['description'] ?? ''), $descriptionCharsPerLine);
        $articleUnits = $this->estimateRpciCellUnits((string) ($row['article'] ?? ''), $articleCharsPerLine);
        $remarksUnits = $this->estimateRpciCellUnits((string) ($row['remarks'] ?? ''), $remarksCharsPerLine);

        return max(1, $descriptionUnits, $articleUnits, $remarksUnits);
    }

    private function estimateRpciCellUnits(string $text, int $charsPerLine): int
    {
        $normalizedText = str_replace(["\r\n", "\r"], "\n", $text);
        $segments = explode("\n", $normalizedText);
        $units = 0;

        foreach ($segments as $segment) {
            $value = preg_replace('/\s+/u', ' ', trim($segment)) ?? '';
            $units += max(1, (int) ceil(mb_strlen($value) / max(1, $charsPerLine)));
        }

        return max(1, $units);
    }

    /**
     * @param  Collection<int, FundSource>  $activeFundSources
     */
    private function resolveSsmiFundSourceId(Collection $activeFundSources, ?string $requestedFundSourceId): string
    {
        $requested = $this->normalizeNullableId($requestedFundSourceId);

        if ($requested !== null) {
            $match = $activeFundSources->first(function (FundSource $fundSource) use ($requested): bool {
                return (string) $fundSource->id === $requested;
            });

            abort_if(! $match, 404, 'The selected fund source is not available for SSMI printing.');

            return $requested;
        }

        $generalFund = $activeFundSources->first(function (FundSource $fundSource): bool {
            $code = Str::lower(trim((string) ($fundSource->code ?? '')));
            $name = Str::lower(trim((string) ($fundSource->name ?? '')));

            return $code === 'general fund' || $name === 'general fund';
        });

        return (string) ($generalFund?->id ?? $activeFundSources->first()?->id ?? '');
    }

    /**
     * @return array{0:Carbon,1:Carbon}
     */
    private function resolveSsmiPeriod(?string $dateFrom, ?string $dateTo): array
    {
        $dateFrom = trim((string) ($dateFrom ?? ''));
        $dateTo = trim((string) ($dateTo ?? ''));

        $to = $dateTo !== '' ? Carbon::parse($dateTo) : Carbon::today();
        $from = $dateFrom !== '' ? Carbon::parse($dateFrom) : $to->copy()->startOfMonth();

        if ($from->gt($to)) {
            throw ValidationException::withMessages([
                'date_to' => 'Date To must be on or after Date From.',
            ]);
        }

        return [$from->copy()->startOfDay(), $to->copy()->endOfDay()];
    }

    /**
     * @return array{0:array<int, array<string, mixed>>,1:array<string, int|float>}
     */
    private function buildSsmiRows(string $fundSourceId, Carbon $periodFrom, Carbon $periodTo): array
    {
        $issueRows = DB::table('ris_items as ri')
            ->join('ris as r', function ($join) {
                $join->on('r.id', '=', 'ri.ris_id')
                    ->whereNull('r.deleted_at');
            })
            ->leftJoin('departments as d', function ($join) {
                $join->on('d.id', '=', 'r.requesting_department_id')
                    ->whereNull('d.deleted_at');
            })
            ->leftJoin('items as i', function ($join) {
                $join->on('i.id', '=', 'ri.item_id')
                    ->whereNull('i.deleted_at');
            })
            ->whereNull('ri.deleted_at')
            ->whereRaw("LOWER(COALESCE(r.status, '')) = 'issued'")
            ->where('r.fund_source_id', $fundSourceId)
            ->where('ri.qty_issued', '>', 0)
            ->whereDate(DB::raw('COALESCE(r.issued_by_date, r.ris_date)'), '>=', $periodFrom->toDateString())
            ->whereDate(DB::raw('COALESCE(r.issued_by_date, r.ris_date)'), '<=', $periodTo->toDateString())
            ->orderByRaw('COALESCE(r.issued_by_date, r.ris_date) asc')
            ->orderBy('r.ris_number')
            ->orderByRaw('COALESCE(ri.line_no, 999999) asc')
            ->select([
                DB::raw('COALESCE(r.issued_by_date, r.ris_date) as issue_date'),
                'ri.id as line_id',
                'ri.ris_id',
                'ri.item_id',
                'ri.line_no',
                'ri.qty_issued',
                'ri.stock_no_snapshot',
                'ri.description_snapshot',
                'ri.unit_snapshot',
                'r.ris_number',
                'r.requesting_department_code_snapshot',
                'r.requesting_department_name_snapshot',
                'd.code as department_code',
                'd.name as department_name',
                'i.item_name',
                'i.item_identification',
                'i.description as item_description',
                'i.base_unit',
            ])
            ->get();

        if ($issueRows->isEmpty()) {
            return [[], [
                'total_ris' => 0,
                'total_lines' => 0,
                'total_qty' => 0,
                'total_cost' => 0.0,
            ]];
        }

        $itemIds = $issueRows
            ->pluck('item_id')
            ->filter(fn ($itemId) => trim((string) $itemId) !== '')
            ->map(fn ($itemId) => (string) $itemId)
            ->unique()
            ->values()
            ->all();

        $valuationMap = $this->buildSsmiIssueValuationMap(
            fundSourceId: $fundSourceId,
            itemIds: $itemIds,
            periodFrom: $periodFrom,
            periodTo: $periodTo,
        );

        $rows = [];
        $totalQty = 0;
        $totalCost = 0.0;
        $risIds = [];
        $lineNo = 1;

        foreach ($issueRows as $row) {
            $qtyIssued = max(0, (int) ($row->qty_issued ?? 0));
            $valuation = $valuationMap[(string) ($row->line_id ?? '')] ?? [
                'unit_cost' => 0.0,
                'total_cost' => 0.0,
            ];

            $unitCost = round((float) ($valuation['unit_cost'] ?? 0.0), 4);
            $lineTotal = round((float) ($valuation['total_cost'] ?? 0.0), 2);

            $rows[] = [
                'line_no' => $lineNo++,
                'issue_date' => $row->issue_date
                    ? Carbon::parse($row->issue_date)->format('m/d/Y')
                    : '',
                'ris_number' => trim((string) ($row->ris_number ?? '')),
                'office' => $this->buildSsmiOfficeLabel($row),
                'stock_no' => trim((string) ($row->stock_no_snapshot ?? $row->item_identification ?? '')),
                'description' => $this->buildSsmiDescription($row),
                'unit' => trim((string) ($row->unit_snapshot ?? $row->base_unit ?? '')),
                'qty_issued' => $qtyIssued,
                'unit_cost' => $unitCost,
                'total_cost' => $lineTotal,
            ];

            $totalQty += $qtyIssued;
            $totalCost += $lineTotal;
            $risIds[(string) ($row->ris_id ?? '')] = true;
        }

        return [$rows, [
            'total_ris' => count(array_filter(array_keys($risIds))),
            'total_lines' => count($rows),
            'total_qty' => $totalQty,
            'total_cost' => round($totalCost, 2),
        ]];
    }

    /**
     * @param  array<int, string>  $itemIds
     * @return array<string, array{unit_cost: float, total_cost: float}>
     */
    private function buildSsmiIssueValuationMap(
        string $fundSourceId,
        array $itemIds,
        Carbon $periodFrom,
        Carbon $periodTo,
    ): array {
        if ($itemIds === []) {
            return [];
        }

        $receiptRows = DB::table('stock_movements as sm')
            ->join('air_items as ai', 'ai.id', '=', 'sm.air_item_id')
            ->leftJoin('items as i', function ($join) {
                $join->on('i.id', '=', 'sm.item_id')
                    ->whereNull('i.deleted_at');
            })
            ->leftJoin('item_unit_conversions as iuc', function ($join) {
                $join->on('iuc.item_id', '=', 'ai.item_id')
                    ->on('iuc.from_unit', '=', 'ai.unit_snapshot')
                    ->whereNull('iuc.deleted_at');
            })
            ->whereNull('sm.deleted_at')
            ->where('sm.movement_type', StockMovementTypes::IN)
            ->where('sm.fund_source_id', $fundSourceId)
            ->whereIn('sm.item_id', $itemIds)
            ->where('sm.occurred_at', '<=', $periodTo->copy()->endOfDay())
            ->orderBy('sm.occurred_at')
            ->orderBy('sm.created_at')
            ->select([
                'sm.item_id',
                'sm.qty as base_qty',
                'sm.occurred_at',
                'ai.acquisition_cost',
                'ai.unit_snapshot',
                'i.base_unit',
                'iuc.multiplier',
            ])
            ->get();

        $issueRows = DB::table('ris_items as ri')
            ->join('ris as r', function ($join) {
                $join->on('r.id', '=', 'ri.ris_id')
                    ->whereNull('r.deleted_at');
            })
            ->whereNull('ri.deleted_at')
            ->whereRaw("LOWER(COALESCE(r.status, '')) = 'issued'")
            ->where('r.fund_source_id', $fundSourceId)
            ->whereIn('ri.item_id', $itemIds)
            ->where('ri.qty_issued', '>', 0)
            ->whereDate(DB::raw('COALESCE(r.issued_by_date, r.ris_date)'), '<=', $periodTo->toDateString())
            ->orderByRaw('COALESCE(r.issued_by_date, r.ris_date) asc')
            ->orderBy('r.ris_number')
            ->orderByRaw('COALESCE(ri.line_no, 999999) asc')
            ->select([
                DB::raw('COALESCE(r.issued_by_date, r.ris_date) as issue_date'),
                'ri.id as line_id',
                'ri.item_id',
                'ri.qty_issued',
            ])
            ->get();

        $eventsByItem = [];
        $sequence = 0;

        foreach ($receiptRows as $receipt) {
            $itemId = (string) ($receipt->item_id ?? '');
            if ($itemId === '') {
                continue;
            }

            $baseQty = max(0, (int) ($receipt->base_qty ?? 0));
            if ($baseQty <= 0) {
                continue;
            }

            $multiplier = 1;
            $baseUnit = trim((string) ($receipt->base_unit ?? ''));
            $receiptUnit = trim((string) ($receipt->unit_snapshot ?? ''));
            if ($receiptUnit !== '' && $baseUnit !== '' && strcasecmp($receiptUnit, $baseUnit) !== 0) {
                $multiplier = max(1, (int) ($receipt->multiplier ?? 1));
            }

            $acquisitionCost = (float) ($receipt->acquisition_cost ?? 0);
            $unitCost = $multiplier > 0 ? ($acquisitionCost / $multiplier) : $acquisitionCost;

            $eventsByItem[$itemId][] = [
                'sort_at' => Carbon::parse($receipt->occurred_at)->toDateTimeString(),
                'priority' => 0,
                'sequence' => $sequence++,
                'type' => 'receipt',
                'qty' => $baseQty,
                'unit_cost' => $unitCost > 0 ? $unitCost : 0.0,
            ];
        }

        foreach ($issueRows as $issue) {
            $itemId = (string) ($issue->item_id ?? '');
            if ($itemId === '') {
                continue;
            }

            $qtyIssued = max(0, (int) ($issue->qty_issued ?? 0));
            if ($qtyIssued <= 0) {
                continue;
            }

            $eventsByItem[$itemId][] = [
                'sort_at' => Carbon::parse((string) $issue->issue_date)->endOfDay()->toDateTimeString(),
                'priority' => 1,
                'sequence' => $sequence++,
                'type' => 'issue',
                'qty' => $qtyIssued,
                'line_id' => (string) ($issue->line_id ?? ''),
            ];
        }

        $periodStart = $periodFrom->toDateString();
        $periodEnd = $periodTo->toDateString();
        $valuations = [];

        foreach ($eventsByItem as $events) {
            usort($events, function (array $a, array $b): int {
                $timeCompare = strcmp((string) ($a['sort_at'] ?? ''), (string) ($b['sort_at'] ?? ''));
                if ($timeCompare !== 0) {
                    return $timeCompare;
                }

                $priorityCompare = ((int) ($a['priority'] ?? 0)) <=> ((int) ($b['priority'] ?? 0));
                if ($priorityCompare !== 0) {
                    return $priorityCompare;
                }

                return ((int) ($a['sequence'] ?? 0)) <=> ((int) ($b['sequence'] ?? 0));
            });

            $qtyOnHand = 0;
            $valueOnHand = 0.0;
            $lastUnitCost = 0.0;

            foreach ($events as $event) {
                $qty = max(0, (int) ($event['qty'] ?? 0));
                if ($qty <= 0) {
                    continue;
                }

                if (($event['type'] ?? '') === 'receipt') {
                    $unitCost = max(0.0, (float) ($event['unit_cost'] ?? 0.0));
                    $qtyOnHand += $qty;
                    $valueOnHand = round($valueOnHand + ($qty * $unitCost), 6);
                    if ($unitCost > 0) {
                        $lastUnitCost = $unitCost;
                    }
                    continue;
                }

                $averageUnitCost = $qtyOnHand > 0
                    ? round($valueOnHand / $qtyOnHand, 6)
                    : $lastUnitCost;

                $issueValue = round($qty * $averageUnitCost, 6);

                $qtyOnHand = max(0, $qtyOnHand - $qty);
                $valueOnHand = max(0.0, round($valueOnHand - $issueValue, 6));

                if ($averageUnitCost > 0) {
                    $lastUnitCost = $averageUnitCost;
                }

                $eventDate = substr((string) ($event['sort_at'] ?? ''), 0, 10);
                $lineId = (string) ($event['line_id'] ?? '');

                if ($lineId !== '' && $eventDate >= $periodStart && $eventDate <= $periodEnd) {
                    $valuations[$lineId] = [
                        'unit_cost' => round($averageUnitCost, 4),
                        'total_cost' => round($issueValue, 2),
                    ];
                }
            }
        }

        return $valuations;
    }

    private function buildSsmiOfficeLabel(object $row): string
    {
        $code = trim((string) (
            $row->requesting_department_code_snapshot
            ?? $row->department_code
            ?? ''
        ));
        $name = trim((string) (
            $row->requesting_department_name_snapshot
            ?? $row->department_name
            ?? ''
        ));

        if ($code !== '' && $name !== '') {
            return sprintf('%s - %s', $code, $name);
        }

        if ($code !== '') {
            return $code;
        }

        return $name !== '' ? $name : 'Unspecified Office';
    }

    private function buildSsmiDescription(object $row): string
    {
        $snapshotDescription = trim((string) ($row->description_snapshot ?? ''));
        if ($snapshotDescription !== '') {
            return $snapshotDescription;
        }

        $itemName = trim((string) ($row->item_name ?? ''));
        $itemDescription = trim((string) ($row->item_description ?? ''));

        if ($itemName !== '' && $itemDescription !== '' && stripos($itemDescription, $itemName) === false) {
            return sprintf('%s - %s', $itemName, $itemDescription);
        }

        if ($itemName !== '') {
            return $itemName;
        }

        return $itemDescription !== '' ? $itemDescription : 'Item';
    }

    private function buildSsmiSignatories(array $signatories): array
    {
        $defaults = [
            'prepared_by_name' => (string) config('gso.gso_designate_name', ''),
            'prepared_by_designation' => (string) config('gso.gso_designate_designation', ''),
            'prepared_by_date' => Carbon::today()->toDateString(),
            'certified_by_name' => '',
            'certified_by_designation' => '',
            'certified_by_date' => Carbon::today()->toDateString(),
        ];

        $resolved = [];
        foreach ($defaults as $key => $defaultValue) {
            $value = $signatories[$key] ?? $defaultValue;
            $resolved[$key] = trim((string) ($value ?? ''));
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $paperOverrides
     * @return array<string, mixed>
     */
    private function resolveSsmiPaperProfile(?string $requestedPaper, array $paperOverrides): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_ssmi', $requestedPaper);

        foreach (self::PAPER_OVERRIDE_KEYS as $key) {
            if (! array_key_exists($key, $paperOverrides)) {
                continue;
            }

            $value = $paperOverrides[$key];
            if (! is_int($value)) {
                continue;
            }

            $paperProfile[$key] = $value;
        }

        return $paperProfile;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $paperProfile
     * @return array<string, mixed>
     */
    private function buildSsmiPagination(array $rows, array $paperProfile): array
    {
        $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 18));
        $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? $rowsPerPage));
        $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 8));
        $descriptionCharsPerLine = max(1, (int) ($paperProfile['description_chars_per_line'] ?? 44));
        $pages = [];
        $cursor = 0;

        if ($rows === []) {
            $pages[] = [
                'rows' => [],
                'used_units' => 0,
            ];
        } else {
            while ($cursor < count($rows)) {
                $pageRows = [];
                $usedUnits = 0;

                while ($cursor < count($rows)) {
                    $row = $rows[$cursor];
                    $rowUnits = $this->estimateSsmiRowUnits($row, $descriptionCharsPerLine);

                    if ($pageRows !== [] && ($usedUnits + $rowUnits) > $rowsPerPage) {
                        break;
                    }

                    $pageRows[] = $row;
                    $usedUnits += $rowUnits;
                    $cursor++;

                    if ($usedUnits >= $rowsPerPage) {
                        break;
                    }
                }

                if ($pageRows === []) {
                    $row = $rows[$cursor];
                    $pageRows[] = $row;
                    $usedUnits = $this->estimateSsmiRowUnits($row, $descriptionCharsPerLine);
                    $cursor++;
                }

                $pages[] = [
                    'rows' => $pageRows,
                    'used_units' => $usedUnits,
                ];
            }
        }

        $pageRowCounts = array_map(
            static fn (array $page): int => count($page['rows'] ?? []),
            $pages,
        );
        $pageUsedUnits = array_map(
            static fn (array $page): int => (int) ($page['used_units'] ?? 0),
            $pages,
        );
        $lastUsedUnits = (int) ($pageUsedUnits !== [] ? end($pageUsedUnits) : 0);

        return [
            'pages' => $pages,
            'stats' => [
                'page_count' => max(1, count($pages)),
                'rows_per_page' => $rowsPerPage,
                'grid_rows' => $gridRows,
                'last_page_grid_rows' => $lastPageGridRows,
                'description_chars_per_line' => $descriptionCharsPerLine,
                'page_row_counts' => $pageRowCounts,
                'page_used_units' => $pageUsedUnits,
                'last_page_padding' => $lastPageGridRows > 0
                    ? max(0, $lastPageGridRows - $lastUsedUnits)
                    : 0,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function estimateSsmiRowUnits(array $row, int $descriptionCharsPerLine): int
    {
        $officeCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (16 / 26)));
        $stockNoCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (10 / 26)));

        $descriptionUnits = $this->estimateSsmiCellUnits((string) ($row['description'] ?? ''), $descriptionCharsPerLine);
        $officeUnits = $this->estimateSsmiCellUnits((string) ($row['office'] ?? ''), $officeCharsPerLine);
        $stockNoUnits = $this->estimateSsmiCellUnits((string) ($row['stock_no'] ?? ''), $stockNoCharsPerLine);

        return max(1, $descriptionUnits, $officeUnits, $stockNoUnits);
    }

    private function estimateSsmiCellUnits(string $text, int $charsPerLine): int
    {
        $normalizedText = str_replace(["\r\n", "\r"], "\n", $text);
        $segments = explode("\n", $normalizedText);
        $units = 0;

        foreach ($segments as $segment) {
            $value = preg_replace('/\s+/u', ' ', trim($segment)) ?? '';
            $units += max(1, (int) ceil(mb_strlen($value) / max(1, $charsPerLine)));
        }

        return max(1, $units);
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

    /**
     * @param  array<string, mixed>  $signatories
     * @return array<string, string>
     */
    private function buildRpciSignatories(array $signatories, ?AccountableOfficer $selectedOfficer = null): array
    {
        $defaults = [
            'accountable_officer_name' => (string) ($selectedOfficer?->full_name ?? config('gso.gso_designate_name', '')),
            'accountable_officer_designation' => (string) ($selectedOfficer?->designation ?? config('gso.gso_designate_designation', '')),
            'date_of_assumption' => Carbon::today()->toDateString(),
            'committee_chair_name' => '',
            'committee_member_1_name' => '',
            'committee_member_2_name' => '',
            'approved_by_name' => '',
            'approved_by_designation' => '',
            'verified_by_name' => '',
            'verified_by_designation' => '',
        ];

        $resolved = [];
        foreach ($defaults as $key => $defaultValue) {
            $resolved[$key] = trim((string) ($signatories[$key] ?? $defaultValue));
        }

        return $resolved;
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
