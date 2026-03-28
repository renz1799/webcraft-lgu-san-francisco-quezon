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
use App\Modules\GSO\Services\Contracts\RrspReportServiceInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\InventoryStatuses;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RrspReportService implements RrspReportServiceInterface
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
        ?string $returnDate = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = [],
    ): array {
        $returnAt = $this->resolveAsOfDate($returnDate);

        $activeFundSources = $this->activeFundSources();
        $departments = $this->activeDepartments();
        $accountableOfficers = $this->activeAccountableOfficers();

        $selectedFund = $this->resolveSelectedFundSource($activeFundSources, $fundSourceId, 'RRSP');
        $selectedDepartment = $this->resolveSelectedDepartment($departments, $departmentId, 'RRSP');
        $selectedOfficer = $this->resolveSelectedOfficer($accountableOfficers, $accountableOfficerId, 'RRSP');

        [$rows, $summary] = $this->buildRows(
            returnAt: $returnAt,
            selectedFund: $selectedFund,
            selectedDepartment: $selectedDepartment,
            selectedOfficer: $selectedOfficer,
        );

        $document = [
            'entity_name' => config('print.entity_name')
                ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
            'appendix_label' => 'RRSP',
            'fund_source_id' => $selectedFund?->id ? (string) $selectedFund->id : '',
            'department_id' => $selectedDepartment?->id ? (string) $selectedDepartment->id : '',
            'accountable_officer_id' => $selectedOfficer?->id ? (string) $selectedOfficer->id : '',
            'fund_source' => $selectedFund
                ? $this->buildFundSourceLabel($selectedFund->code, $selectedFund->name)
                : 'All Fund Sources',
            'fund_cluster' => $this->resolveFundClusterLabel($selectedFund, $rows),
            'department' => $selectedDepartment
                ? $this->buildDepartmentScopeLabel($selectedDepartment)
                : 'All Offices',
            'accountable_officer' => $selectedOfficer?->full_name ?: 'All Accountable Officers',
            'return_date' => $returnAt->toDateString(),
            'return_date_label' => $returnAt->format('F j, Y'),
            'summary' => $summary,
            'signatories' => $this->buildSignatories($signatories, $selectedOfficer),
        ];

        $paperProfile = $this->resolveRrspPaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildRrspPagination($rows, $paperProfile);

        return [
            'report' => [
                'title' => 'Receipt of Returned Semi-Expendable Property',
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
        ?string $returnDate = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = [],
    ): string {
        $payload = $this->getPrintViewData(
            fundSourceId: $fundSourceId,
            departmentId: $departmentId,
            accountableOfficerId: $accountableOfficerId,
            returnDate: $returnDate,
            signatories: $signatories,
            requestedPaper: $requestedPaper,
            paperOverrides: $paperOverrides,
        );

        $filename = 'rrsp-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::reports.rrsp.print.pdf',
            data: [
                'report' => $payload['report'],
                'paperProfile' => $payload['paperProfile'],
            ],
            outputPath: $outputPath,
        );
    }

    /**
     * @return array{0:array<int, array<string, mixed>>, 1:array<string, int|float>}
     */
    private function buildRows(
        Carbon $returnAt,
        ?FundSource $selectedFund,
        ?Department $selectedDepartment,
        ?AccountableOfficer $selectedOfficer
    ): array {
        $inventoryItems = $this->inventoryItemsBaseQuery($returnAt, $selectedFund)
            ->where('is_ics', true)
            ->orderBy('property_number')
            ->orderBy('description')
            ->get();

        $rows = [];
        foreach ($inventoryItems as $inventoryItem) {
            /** @var InventoryItemEvent|null $latestEvent */
            $latestEvent = $inventoryItem->events->first();

            if ($this->shouldExcludeItem($inventoryItem, $latestEvent)) {
                continue;
            }

            $currentDepartmentId = $this->nullableTrim($latestEvent?->department_id)
                ?? $this->nullableTrim($inventoryItem->department_id)
                ?? '';
            $currentOfficerName = $this->resolveAccountableOfficerSnapshotName($inventoryItem, $latestEvent);

            if ($selectedDepartment && $currentDepartmentId !== (string) $selectedDepartment->id) {
                continue;
            }

            if ($selectedOfficer && ! $this->matchesOfficer($inventoryItem, $currentOfficerName, $selectedOfficer)) {
                continue;
            }

            $qty = max(1, (int) ($inventoryItem->quantity ?? 1));
            $totalValue = round((float) ($inventoryItem->acquisition_cost ?? 0), 2);
            $unitValue = $qty > 0 ? round($totalValue / $qty, 2) : $totalValue;

            $rows[] = [
                'property_no' => $this->nullableTrim($inventoryItem->property_number) ?? '',
                'article' => $this->buildInventoryItemArticleLabel($inventoryItem, 'Semi-Expendable Property'),
                'description' => $this->buildInventoryItemDescription($inventoryItem, 'Semi-Expendable Property'),
                'unit' => $this->nullableTrim($inventoryItem->unit) ?? '',
                'qty_returned' => $qty,
                'unit_value' => $unitValue,
                'total_value' => $totalValue,
                'condition' => $this->formatSimpleLabel((string) ($latestEvent?->condition ?? $inventoryItem->condition ?? '')) ?? '',
                'office' => $this->nullableTrim($latestEvent?->office_snapshot)
                    ?? $this->buildDepartmentOfficeLabel($latestEvent?->department)
                    ?? $this->buildDepartmentOfficeLabel($inventoryItem->department)
                    ?? '',
                'accountable_officer' => $currentOfficerName,
                'remarks' => $this->buildRemarks($latestEvent, $inventoryItem),
                'fund_cluster_label' => $this->buildFundClusterLabel(
                    $inventoryItem->fundSource?->fundCluster?->code,
                    $inventoryItem->fundSource?->fundCluster?->name,
                ),
            ];
        }

        usort($rows, function (array $left, array $right): int {
            $leftKey = [
                mb_strtolower((string) ($left['office'] ?? '')),
                mb_strtolower((string) ($left['accountable_officer'] ?? '')),
                mb_strtolower((string) ($left['article'] ?? '')),
                mb_strtolower((string) ($left['property_no'] ?? '')),
            ];

            $rightKey = [
                mb_strtolower((string) ($right['office'] ?? '')),
                mb_strtolower((string) ($right['accountable_officer'] ?? '')),
                mb_strtolower((string) ($right['article'] ?? '')),
                mb_strtolower((string) ($right['property_no'] ?? '')),
            ];

            return $leftKey <=> $rightKey;
        });

        $summary = [
            'items_listed' => count($rows),
            'total_qty_returned' => array_sum(array_map(
                fn (array $row): int => (int) ($row['qty_returned'] ?? 0),
                $rows
            )),
            'total_value' => round(array_sum(array_map(
                fn (array $row): float => (float) ($row['total_value'] ?? 0),
                $rows
            )), 2),
        ];

        return [$rows, $summary];
    }

    private function shouldExcludeItem(InventoryItem $inventoryItem, ?InventoryItemEvent $latestEvent): bool
    {
        if ((bool) ($inventoryItem->is_ics ?? false) !== true) {
            return true;
        }

        $latestEventType = $this->normalizeValue((string) ($latestEvent?->event_type ?? ''));
        $latestStatus = $this->normalizeValue((string) ($latestEvent?->status ?? $inventoryItem->status ?? ''));
        $custodyState = $this->normalizeValue((string) ($inventoryItem->custody_state ?? ''));

        if (in_array($latestStatus, [
            InventoryStatuses::DISPOSED,
            InventoryStatuses::LOST,
            InventoryStatuses::RETURNED,
        ], true)) {
            return true;
        }

        if (in_array($latestEventType, [
            InventoryEventTypes::DISPOSED,
            InventoryEventTypes::RETURNED,
            InventoryEventTypes::TRANSFERRED_OUT,
        ], true)) {
            return true;
        }

        return $custodyState !== $this->normalizeValue(InventoryCustodyStates::ISSUED);
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

    private function buildRemarks(?InventoryItemEvent $latestEvent, InventoryItem $inventoryItem): string
    {
        $status = $this->formatSimpleLabel((string) ($latestEvent?->status ?? $inventoryItem->status ?? ''));

        if ($status === null || $this->normalizeValue($status) === InventoryStatuses::SERVICEABLE) {
            return '';
        }

        return 'Status: ' . $status;
    }

    private function buildSignatories(array $signatories, ?AccountableOfficer $selectedOfficer): array
    {
        $defaults = [
            'returned_by_name' => (string) ($selectedOfficer?->full_name ?? ''),
            'returned_by_designation' => (string) ($selectedOfficer?->designation ?? ''),
            'received_by_name' => (string) config('gso.gso_designate_name', ''),
            'received_by_designation' => (string) config('gso.gso_designate_designation', ''),
            'noted_by_name' => '',
            'noted_by_designation' => '',
        ];

        $resolved = [];
        foreach ($defaults as $key => $defaultValue) {
            $resolved[$key] = $this->nullableTrim($signatories[$key] ?? $defaultValue) ?? '';
        }

        return $resolved;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function resolveFundClusterLabel(?FundSource $selectedFund, array $rows): string
    {
        if ($selectedFund) {
            return $this->buildFundClusterLabel(
                $selectedFund->fundCluster?->code,
                $selectedFund->fundCluster?->name,
            );
        }

        $labels = array_values(array_unique(array_filter(array_map(
            fn (array $row): string => trim((string) ($row['fund_cluster_label'] ?? '')),
            $rows
        ))));

        if (count($labels) === 1) {
            return $labels[0];
        }

        return 'All / Multiple';
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $paperProfile
     * @return array<string, mixed>
     */
    private function buildRrspPagination(array $rows, array $paperProfile): array
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
                    $rowUnits = $this->estimateRrspRowUnits($row, $descriptionCharsPerLine);

                    if ($pageRows !== [] && ($usedUnits + $rowUnits) > $rowsPerPage) {
                        break;
                    }

                    $pageRows[] = $row;
                    $usedUnits += $rowUnits;
                    $cursor++;
                }

                if ($pageRows === []) {
                    $pageRows[] = $rows[$cursor];
                    $usedUnits = $this->estimateRrspRowUnits($rows[$cursor], $descriptionCharsPerLine);
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
    private function estimateRrspRowUnits(array $row, int $descriptionCharsPerLine): int
    {
        $maxUnits = max(
            $this->estimateRrspCellUnits($row['property_no'] ?? '', max(10, (int) round($descriptionCharsPerLine * 0.45))),
            $this->estimateRrspCellUnits($row['article'] ?? '', max(12, (int) round($descriptionCharsPerLine * 0.55))),
            $this->estimateRrspCellUnits($row['description'] ?? '', $descriptionCharsPerLine),
            $this->estimateRrspCellUnits($row['condition'] ?? '', max(10, (int) round($descriptionCharsPerLine * 0.4))),
            $this->estimateRrspCellUnits($row['office'] ?? '', max(10, (int) round($descriptionCharsPerLine * 0.45))),
            $this->estimateRrspCellUnits(
                trim(((string) ($row['accountable_officer'] ?? '')) . "\n" . ((string) ($row['remarks'] ?? ''))),
                max(10, (int) round($descriptionCharsPerLine * 0.45))
            ),
        );

        return max(1, $maxUnits);
    }

    private function estimateRrspCellUnits(mixed $value, int $charsPerLine): int
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
    private function resolveRrspPaperProfile(?string $requestedPaper, array $overrides): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_rrsp', $requestedPaper);

        foreach (self::PAPER_OVERRIDE_KEYS as $key) {
            if (array_key_exists($key, $overrides) && is_int($overrides[$key])) {
                $paperProfile[$key] = $overrides[$key];
            }
        }

        return $paperProfile;
    }
}
