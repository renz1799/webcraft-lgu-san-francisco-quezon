<?php

namespace App\Modules\GSO\Services\ITR;

use App\Core\Models\Department;
use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Models\ItrItem;
use App\Modules\GSO\Services\Contracts\ITR\ItrPrintServiceInterface;
use Illuminate\Support\Str;

class ItrPrintService implements ItrPrintServiceInterface
{
    private const PAPER_OVERRIDE_KEYS = [
        'rows_per_page',
        'grid_rows',
        'last_page_grid_rows',
        'description_chars_per_line',
    ];

    public function __construct(
        private readonly PdfGeneratorInterface $pdfGenerator,
        private readonly PrintConfigLoaderInterface $printConfigLoader,
    ) {
    }

    public function buildReport(string $itrId, ?string $requestedPaper = null, array $paperOverrides = []): array
    {
        $itr = Itr::query()
            ->withTrashed()
            ->with([
                'fromDepartment' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'toDepartment' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'fromFundSource' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name', 'fund_cluster_id']),
                'toFundSource' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name', 'fund_cluster_id']),
                'fromFundSource.fundCluster' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'toFundSource.fundCluster' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'items' => fn ($query) => $query
                    ->orderBy('line_no')
                    ->orderBy('created_at'),
                'items.inventoryItem' => fn ($query) => $query->withTrashed(),
                'items.inventoryItem.item' => fn ($query) => $query->withTrashed(),
            ])
            ->findOrFail($itrId);

        abort_if(
            (string) ($itr->status ?? '') !== 'finalized',
            409,
            'Only finalized ITR can be printed.'
        );

        $rows = $this->buildRows($itr);
        $paperProfile = $this->resolvePaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildPagination($rows, $paperProfile);
        $report = [
            'title' => 'Inventory Transfer Report',
            'itr' => $this->buildItrSummary($itr),
            'document' => $this->buildDocumentMeta($itr, $rows),
            'rows' => $rows,
            'pagination' => $pagination,
        ];

        return [
            'report' => $report,
            'paperProfile' => $paperProfile,
        ];
    }

    public function generatePdf(string $itrId, ?string $requestedPaper = null, array $paperOverrides = []): string
    {
        $payload = $this->buildReport($itrId, $requestedPaper, $paperOverrides);

        $filename = 'itr-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::itrs.print.pdf',
            data: [
                'report' => $payload['report'],
                'paperProfile' => $payload['paperProfile'],
            ],
            outputPath: $outputPath,
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRows(Itr $itr): array
    {
        return $itr->items
            ->sortBy(fn (ItrItem $item) => [
                (int) ($item->line_no ?? 999999),
                (string) $item->created_at,
            ])
            ->values()
            ->map(function (ItrItem $item): array {
                $inventoryItem = $item->inventoryItem;
                $description = $this->nullableTrim($inventoryItem?->description)
                    ?? $this->nullableTrim($item->description_snapshot)
                    ?? $this->nullableTrim($item->item_name_snapshot)
                    ?? $this->nullableTrim($inventoryItem?->item?->item_name)
                    ?? '';

                return [
                    'quantity' => max(1, (int) ($item->quantity ?? 1)),
                    'date_acquired_label' => $item->date_acquired_snapshot?->format('m/d/Y')
                        ?? $inventoryItem?->acquisition_date?->format('m/d/Y')
                        ?? '',
                    'inventory_item_no' => $this->nullableTrim($item->inventory_item_no_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->property_number)
                        ?? $this->nullableTrim($inventoryItem?->inventory_item_no)
                        ?? '',
                    'description' => $description,
                    'amount' => $item->amount_snapshot !== null ? (float) $item->amount_snapshot : null,
                    'estimated_useful_life' => $this->nullableTrim($item->estimated_useful_life_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->estimated_useful_life)
                        ?? $this->nullableTrim($inventoryItem?->service_life)
                        ?? '',
                    'condition' => $this->nullableTrim($item->condition_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->condition)
                        ?? '',
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function buildDocumentMeta(Itr $itr, array $rows): array
    {
        return [
            'entity_name' => $this->nullableTrim($itr->entity_name_snapshot)
                ?? config('print.entity_name')
                ?? config('app.lgu_name', config('app.name', 'Local Government Unit')),
            'appendix_label' => '',
            'title' => 'Inventory Transfer Report',
            'fund_cluster' => $this->nullableTrim($itr->header_fund_cluster_code_snapshot)
                ?? $this->fundClusterCode($itr, 'from')
                ?? $this->fundClusterCode($itr, 'to')
                ?? '',
            'itr_number' => $this->nullableTrim($itr->itr_number) ?? '',
            'transfer_date_label' => $itr->transfer_date?->format('m/d/Y') ?? '',
            'from_summary' => $this->buildAssignmentSummary(
                $this->nullableTrim($itr->from_department_snapshot) ?? $this->officeSnapshot($itr->fromDepartment),
                $this->nullableTrim($itr->from_accountable_officer),
                $this->nullableTrim($itr->from_fund_cluster_code_snapshot) ?? $this->fundClusterCode($itr, 'from'),
            ),
            'to_summary' => $this->buildAssignmentSummary(
                $this->nullableTrim($itr->to_department_snapshot) ?? $this->officeSnapshot($itr->toDepartment),
                $this->nullableTrim($itr->to_accountable_officer),
                $this->nullableTrim($itr->to_fund_cluster_code_snapshot) ?? $this->fundClusterCode($itr, 'to'),
            ),
            'transfer_type' => $this->nullableTrim($itr->transfer_type) ?? '',
            'transfer_type_other' => $this->nullableTrim($itr->transfer_type_other) ?? '',
            'reason_for_transfer' => $this->nullableTrim($itr->reason_for_transfer) ?? '',
            'approved_by_name' => $this->nullableTrim($itr->approved_by_name) ?? '',
            'approved_by_designation' => $this->nullableTrim($itr->approved_by_designation) ?? '',
            'approved_by_date_label' => $itr->approved_by_date?->format('m/d/Y') ?? '',
            'released_by_name' => $this->nullableTrim($itr->released_by_name) ?? '',
            'released_by_designation' => $this->nullableTrim($itr->released_by_designation) ?? '',
            'released_by_date_label' => $itr->released_by_date?->format('m/d/Y') ?? '',
            'received_by_name' => $this->nullableTrim($itr->received_by_name) ?? '',
            'received_by_designation' => $this->nullableTrim($itr->received_by_designation) ?? '',
            'received_by_date_label' => $itr->received_by_date?->format('m/d/Y') ?? '',
            'summary' => [
                'line_items' => count($rows),
                'printed_rows' => count($rows),
                'quantity_total' => array_sum(array_map(
                    static fn (array $row): int => (int) ($row['quantity'] ?? 0),
                    $rows,
                )),
                'amount_total' => array_sum(array_map(
                    static fn (array $row): float => (float) ($row['amount'] ?? 0),
                    $rows,
                )),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $paperOverrides
     * @return array<string, mixed>
     */
    private function resolvePaperProfile(?string $requestedPaper, array $paperOverrides): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_itr', $requestedPaper);

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
    private function buildPagination(array $rows, array $paperProfile): array
    {
        $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 12));
        $firstPageRows = max(1, (int) ($paperProfile['first_page_rows'] ?? $rowsPerPage));
        $laterPageRows = max(1, (int) ($paperProfile['later_page_rows'] ?? $rowsPerPage));
        $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? $rowsPerPage));
        $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
        $descriptionCharsPerLine = max(1, (int) ($paperProfile['description_chars_per_line'] ?? 34));
        $pages = [];
        $cursor = 0;
        $pageNumber = 0;

        if ($rows === []) {
            $pages[] = [
                'rows' => [],
                'used_units' => 0,
            ];
        } else {
            while ($cursor < count($rows)) {
                $capacity = $pageNumber === 0 ? $firstPageRows : $laterPageRows;
                $pageRows = [];
                $usedUnits = 0;

                while ($cursor < count($rows)) {
                    $row = $rows[$cursor];
                    $rowUnits = $this->estimateRowUnits($row, $descriptionCharsPerLine);

                    if ($pageRows !== [] && ($usedUnits + $rowUnits) > $capacity) {
                        break;
                    }

                    $pageRows[] = $row;
                    $usedUnits += $rowUnits;
                    $cursor++;

                    if ($usedUnits >= $capacity) {
                        break;
                    }
                }

                if ($pageRows === []) {
                    $row = $rows[$cursor];
                    $pageRows[] = $row;
                    $usedUnits = $this->estimateRowUnits($row, $descriptionCharsPerLine);
                    $cursor++;
                }

                $pages[] = [
                    'rows' => $pageRows,
                    'used_units' => $usedUnits,
                ];
                $pageNumber++;
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
                'first_page_rows' => $firstPageRows,
                'later_page_rows' => $laterPageRows,
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
    private function estimateRowUnits(array $row, int $descriptionCharsPerLine): int
    {
        $inventoryCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (16 / 32)));
        $usefulLifeCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (9 / 32)));
        $conditionCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (11 / 32)));

        $descriptionUnits = $this->estimateCellUnits((string) ($row['description'] ?? ''), $descriptionCharsPerLine);
        $inventoryUnits = $this->estimateCellUnits((string) ($row['inventory_item_no'] ?? ''), $inventoryCharsPerLine);
        $usefulLifeUnits = $this->estimateCellUnits((string) ($row['estimated_useful_life'] ?? ''), $usefulLifeCharsPerLine);
        $conditionUnits = $this->estimateCellUnits((string) ($row['condition'] ?? ''), $conditionCharsPerLine);

        return max(1, $descriptionUnits, $inventoryUnits, $usefulLifeUnits, $conditionUnits);
    }

    private function estimateCellUnits(string $text, int $charsPerLine): int
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
     * @return array<string, mixed>
     */
    private function buildItrSummary(Itr $itr): array
    {
        $label = $this->nullableTrim($itr->itr_number)
            ?? $this->nullableTrim($itr->to_accountable_officer)
            ?? $this->nullableTrim($itr->from_accountable_officer)
            ?? 'ITR Record';

        return [
            'id' => (string) $itr->id,
            'label' => $label,
            'status' => (string) ($itr->status ?? ''),
            'status_text' => strtoupper((string) ($itr->status ?? 'draft')),
            'is_archived' => $itr->trashed(),
        ];
    }

    private function officeSnapshot(?Department $department): ?string
    {
        if (! $department) {
            return null;
        }

        $shortName = trim((string) ($department->short_name ?? ''));
        if ($shortName !== '') {
            return $shortName;
        }

        $code = trim((string) ($department->code ?? ''));
        if ($code !== '') {
            return $code;
        }

        $name = trim((string) ($department->name ?? ''));

        return $name !== '' ? $name : null;
    }

    private function fundClusterCode(Itr $itr, string $side): ?string
    {
        $relation = $side === 'to' ? $itr->toFundSource : $itr->fromFundSource;
        $code = trim((string) ($relation?->fundCluster?->code ?? ''));

        return $code !== '' ? $code : null;
    }

    private function buildAssignmentSummary(?string $department, ?string $officer, ?string $fundCluster): string
    {
        $parts = array_values(array_filter([
            $this->nullableTrim($department),
            $this->nullableTrim($officer),
            $this->nullableTrim($fundCluster),
        ]));

        return implode(' / ', $parts);
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
