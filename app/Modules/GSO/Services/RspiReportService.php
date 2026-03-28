<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\InventoryItemEvent;
use App\Modules\GSO\Services\Concerns\BuildsInventoryItemReportSupport;
use App\Modules\GSO\Services\Contracts\RspiReportServiceInterface;
use App\Modules\GSO\Support\InventoryEventTypes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RspiReportService implements RspiReportServiceInterface
{
    use BuildsInventoryItemReportSupport;

    private const PAPER_OVERRIDE_KEYS = [
        'rows_per_page',
        'grid_rows',
        'last_page_grid_rows',
        'description_chars_per_line',
    ];

    public function __construct(
        private readonly PdfGeneratorInterface $pdfGenerator,
        private readonly PrintConfigLoaderInterface $printConfigLoader,
    ) {}

    public function getPrintViewData(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = [],
    ): array {
        [$periodFrom, $periodTo] = $this->resolvePeriod($dateFrom, $dateTo);

        $activeFundSources = $this->activeFundSources();
        $departments = $this->activeDepartments();
        $accountableOfficers = $this->activeAccountableOfficers();

        $selectedFund = $this->resolveSelectedFundSource($activeFundSources, $fundSourceId, 'RSPI');
        $selectedDepartment = $this->resolveSelectedDepartment($departments, $departmentId, 'RSPI');
        $selectedOfficer = $this->resolveSelectedOfficer($accountableOfficers, $accountableOfficerId, 'RSPI');

        [$rows, $summary] = $this->buildRows(
            periodFrom: $periodFrom,
            periodTo: $periodTo,
            selectedFund: $selectedFund,
            selectedDepartment: $selectedDepartment,
            selectedOfficer: $selectedOfficer,
        );

        $document = [
            'entity_name' => config('print.entity_name')
                ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
            'appendix_label' => 'RSPI',
            'fund_source_id' => $selectedFund?->id ? (string) $selectedFund->id : '',
            'department_id' => $selectedDepartment?->id ? (string) $selectedDepartment->id : '',
            'accountable_officer_id' => $selectedOfficer?->id ? (string) $selectedOfficer->id : '',
            'fund_source' => $selectedFund
                ? $this->buildFundSourceLabel($selectedFund->code, $selectedFund->name)
                : 'All Fund Sources',
            'fund_cluster' => $selectedFund
                ? $this->buildFundClusterLabel($selectedFund->fundCluster?->code, $selectedFund->fundCluster?->name)
                : 'All / Multiple',
            'department' => $selectedDepartment
                ? $this->buildDepartmentScopeLabel($selectedDepartment)
                : 'All Offices',
            'accountable_officer' => $selectedOfficer?->full_name ?: 'All Accountable Officers',
            'date_from' => $periodFrom->toDateString(),
            'date_to' => $periodTo->toDateString(),
            'period_label' => $periodFrom->isSameDay($periodTo)
                ? $periodFrom->format('F j, Y')
                : sprintf('%s to %s', $periodFrom->format('F j, Y'), $periodTo->format('F j, Y')),
            'summary' => $summary,
            'signatories' => $this->buildSignatories($signatories),
        ];

        $paperProfile = $this->resolveRspiPaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildRspiPagination($rows, $paperProfile);

        return [
            'report' => [
                'title' => 'Report of Semi-Expendable Property Issued',
                'document' => $document,
                'rows' => $rows,
                'pagination' => $pagination,
            ],
            'paperProfile' => $paperProfile,
            ...$this->buildFilterOptions($activeFundSources, $departments, $accountableOfficers),
        ];
    }

    public function generatePdf(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = [],
    ): string {
        $payload = $this->getPrintViewData(
            fundSourceId: $fundSourceId,
            departmentId: $departmentId,
            accountableOfficerId: $accountableOfficerId,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            signatories: $signatories,
            requestedPaper: $requestedPaper,
            paperOverrides: $paperOverrides,
        );

        $filename = 'rspi-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::reports.rspi.print.pdf',
            data: [
                'report' => $payload['report'],
                'paperProfile' => $payload['paperProfile'],
            ],
            outputPath: $outputPath,
        );
    }

    /**
     * @return array{0:Carbon,1:Carbon}
     */
    private function resolvePeriod(?string $dateFrom, ?string $dateTo): array
    {
        $dateFrom = $this->nullableTrim($dateFrom);
        $dateTo = $this->nullableTrim($dateTo);

        $to = $dateTo !== null ? Carbon::parse($dateTo)->endOfDay() : Carbon::today()->endOfDay();
        $from = $dateFrom !== null ? Carbon::parse($dateFrom)->startOfDay() : $to->copy()->startOfMonth();

        abort_if($from->gt($to), 422, 'Date To must be on or after Date From.');

        return [$from, $to];
    }

    /**
     * @return array{0:array<int, array<string, mixed>>, 1:array<string, int|float>}
     */
    private function buildRows(
        Carbon $periodFrom,
        Carbon $periodTo,
        ?FundSource $selectedFund,
        ?Department $selectedDepartment,
        ?AccountableOfficer $selectedOfficer
    ): array {
        $query = InventoryItemEvent::query()
            ->with([
                'inventoryItem.item:id,item_name',
                'inventoryItem.department:id,code,name,short_name',
                'inventoryItem.fundSource.fundCluster:id,code,name',
                'inventoryItem.accountableOfficerRelation:id,full_name,designation,department_id',
                'department:id,code,name,short_name',
            ])
            ->whereNull('deleted_at')
            ->where('event_type', InventoryEventTypes::ISSUED)
            ->whereRaw("LOWER(COALESCE(reference_type, '')) = 'ics'")
            ->whereDate('event_date', '>=', $periodFrom->toDateString())
            ->whereDate('event_date', '<=', $periodTo->toDateString())
            ->whereHas('inventoryItem', function (Builder $query): void {
                $query->where('is_ics', true)
                    ->whereNull('deleted_at');
            });

        if ($selectedFund) {
            $selectedFundId = (string) $selectedFund->id;

            $query->where(function (Builder $query) use ($selectedFundId): void {
                $query->where('fund_source_id', $selectedFundId)
                    ->orWhere(function (Builder $fallbackQuery) use ($selectedFundId): void {
                        $fallbackQuery->whereNull('fund_source_id')
                            ->whereHas('inventoryItem', function (Builder $inventoryItemQuery) use ($selectedFundId): void {
                                $inventoryItemQuery->where('fund_source_id', $selectedFundId);
                            });
                    });
            });
        }

        if ($selectedDepartment) {
            $query->where('department_id', (string) $selectedDepartment->id);
        }

        $events = $query
            ->orderBy('event_date')
            ->orderBy('reference_no')
            ->orderBy('created_at')
            ->get();

        $rows = [];
        foreach ($events as $event) {
            $inventoryItem = $event->inventoryItem;
            if (! $inventoryItem) {
                continue;
            }

            $accountableOfficer = $this->resolveAccountableOfficerLabel($inventoryItem, $event);
            if ($selectedOfficer && ! $this->matchesOfficer($inventoryItem, $accountableOfficer, $selectedOfficer)) {
                continue;
            }

            $qtyIssued = max(1, (int) ($event->qty_out ?? 0));
            $unitCost = round((float) ($event->amount_snapshot ?? 0), 2);
            $totalCost = round($qtyIssued * $unitCost, 2);

            $rows[] = [
                'date' => $event->event_date?->toDateString() ?? '',
                'reference' => $this->buildReferenceLabel($event),
                'office' => $this->nullableTrim($event->office_snapshot)
                    ?? $this->buildDepartmentOfficeLabel($event->department)
                    ?? $this->buildDepartmentOfficeLabel($inventoryItem->department)
                    ?? '',
                'accountable_officer' => $accountableOfficer,
                'property_no' => $this->nullableTrim($inventoryItem->property_number) ?? '',
                'article' => $this->buildInventoryItemArticleLabel($inventoryItem, 'Semi-Expendable Property'),
                'description' => $this->buildInventoryItemDescription($inventoryItem, 'Semi-Expendable Property'),
                'unit' => $this->nullableTrim($event->unit_snapshot)
                    ?? $this->nullableTrim($inventoryItem->unit)
                    ?? '',
                'qty_issued' => $qtyIssued,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'ics_no' => $this->nullableTrim($event->reference_no) ?? '',
            ];
        }

        $summary = [
            'ics_covered' => count(array_unique(array_values(array_filter(array_map(
                fn (array $row): string => trim((string) ($row['ics_no'] ?? '')),
                $rows
            ))))),
            'lines_count' => count($rows),
            'total_qty_issued' => array_sum(array_map(
                fn (array $row): int => (int) ($row['qty_issued'] ?? 0),
                $rows
            )),
            'total_cost' => round(array_sum(array_map(
                fn (array $row): float => (float) ($row['total_cost'] ?? 0),
                $rows
            )), 2),
        ];

        return [$rows, $summary];
    }

    private function resolveAccountableOfficerLabel(InventoryItem $inventoryItem, InventoryItemEvent $event): string
    {
        return $this->nullableTrim($event->officer_snapshot)
            ?? $this->nullableTrim($event->person_accountable)
            ?? $this->nullableTrim($inventoryItem->accountable_officer)
            ?? $this->nullableTrim($inventoryItem->accountableOfficerRelation?->full_name)
            ?? '';
    }

    private function matchesOfficer(
        InventoryItem $inventoryItem,
        string $currentOfficerName,
        AccountableOfficer $selectedOfficer
    ): bool {
        if ((string) ($inventoryItem->accountable_officer_id ?? '') === (string) $selectedOfficer->id) {
            return true;
        }

        return $this->normalizeValue($currentOfficerName) === $this->normalizeValue((string) ($selectedOfficer->full_name ?? ''));
    }

    private function buildSignatories(array $signatories): array
    {
        $defaults = [
            'prepared_by_name' => (string) config('gso.gso_designate_name', ''),
            'prepared_by_designation' => (string) config('gso.gso_designate_designation', ''),
            'reviewed_by_name' => '',
            'reviewed_by_designation' => '',
            'approved_by_name' => '',
            'approved_by_designation' => '',
        ];

        $resolved = [];
        foreach ($defaults as $key => $defaultValue) {
            $resolved[$key] = $this->nullableTrim($signatories[$key] ?? $defaultValue) ?? '';
        }

        return $resolved;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $paperProfile
     * @return array<string, mixed>
     */
    private function buildRspiPagination(array $rows, array $paperProfile): array
    {
        $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 15));
        $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? $rowsPerPage));
        $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 8));
        $descriptionCharsPerLine = max(1, (int) ($paperProfile['description_chars_per_line'] ?? 52));
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
                    $rowUnits = $this->estimateRspiRowUnits($row, $descriptionCharsPerLine);

                    if ($pageRows !== [] && ($usedUnits + $rowUnits) > $rowsPerPage) {
                        break;
                    }

                    $pageRows[] = $row;
                    $usedUnits += $rowUnits;
                    $cursor++;
                }

                if ($pageRows === []) {
                    $pageRows[] = $rows[$cursor];
                    $usedUnits = $this->estimateRspiRowUnits($rows[$cursor], $descriptionCharsPerLine);
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
        $lastUsedUnits = $pageUsedUnits !== [] ? (int) end($pageUsedUnits) : 0;

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
    private function estimateRspiRowUnits(array $row, int $descriptionCharsPerLine): int
    {
        $maxUnits = max(
            $this->estimateRspiCellUnits($row['reference'] ?? '', max(10, (int) round($descriptionCharsPerLine * 0.5))),
            $this->estimateRspiCellUnits($row['property_no'] ?? '', max(10, (int) round($descriptionCharsPerLine * 0.45))),
            $this->estimateRspiCellUnits($row['article'] ?? '', max(12, (int) round($descriptionCharsPerLine * 0.55))),
            $this->estimateRspiCellUnits($row['description'] ?? '', $descriptionCharsPerLine),
            $this->estimateRspiCellUnits($row['office'] ?? '', max(10, (int) round($descriptionCharsPerLine * 0.45))),
            $this->estimateRspiCellUnits($row['accountable_officer'] ?? '', max(10, (int) round($descriptionCharsPerLine * 0.45))),
        );

        return max(1, $maxUnits);
    }

    private function estimateRspiCellUnits(mixed $value, int $charsPerLine): int
    {
        $text = $this->nullableTrim($value) ?? '';

        if ($text === '') {
            return 1;
        }

        $lines = preg_split('/\R/u', $text) ?: [$text];
        $units = 0;

        foreach ($lines as $line) {
            $units += max(1, (int) ceil(mb_strlen($line) / max(1, $charsPerLine)));
        }

        return max(1, $units);
    }

    /**
     * @param  array<string, int>  $overrides
     * @return array<string, mixed>
     */
    private function resolveRspiPaperProfile(?string $requestedPaper, array $overrides): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_rspi', $requestedPaper);

        foreach (self::PAPER_OVERRIDE_KEYS as $key) {
            if (array_key_exists($key, $overrides) && is_int($overrides[$key])) {
                $paperProfile[$key] = $overrides[$key];
            }
        }

        return $paperProfile;
    }
}
