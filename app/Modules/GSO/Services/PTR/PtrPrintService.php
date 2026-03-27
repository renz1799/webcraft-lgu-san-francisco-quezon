<?php

namespace App\Modules\GSO\Services\PTR;

use App\Core\Models\Department;
use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Models\PtrItem;
use App\Modules\GSO\Services\Contracts\PTR\PtrPrintServiceInterface;
use Illuminate\Support\Str;

class PtrPrintService implements PtrPrintServiceInterface
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

    public function buildReport(string $ptrId, ?string $requestedPaper = null, array $paperOverrides = []): array
    {
        $ptr = Ptr::query()
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
            ->findOrFail($ptrId);

        $rows = $this->buildRows($ptr);
        $paperProfile = $this->resolvePaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildPagination($rows, $paperProfile);
        $report = [
            'title' => 'Property Transfer Report',
            'ptr' => $this->buildPtrSummary($ptr),
            'document' => $this->buildDocumentMeta($ptr, $rows),
            'rows' => $rows,
            'pagination' => $pagination,
        ];

        return [
            'report' => $report,
            'paperProfile' => $paperProfile,
        ];
    }

    public function generatePdf(string $ptrId, ?string $requestedPaper = null, array $paperOverrides = []): string
    {
        $payload = $this->buildReport($ptrId, $requestedPaper, $paperOverrides);

        $filename = 'ptr-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::ptrs.print.pdf',
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
    private function buildRows(Ptr $ptr): array
    {
        return $ptr->items
            ->sortBy(fn (PtrItem $item) => [
                (int) ($item->line_no ?? 999999),
                (string) $item->created_at,
            ])
            ->values()
            ->map(function (PtrItem $item): array {
                $inventoryItem = $item->inventoryItem;
                $itemName = $this->nullableTrim($item->item_name_snapshot)
                    ?? $this->nullableTrim($inventoryItem?->item?->item_name)
                    ?? '';
                $description = $this->nullableTrim($inventoryItem?->description)
                    ?? $this->nullableTrim($item->description_snapshot)
                    ?? $itemName;

                return [
                    'date_acquired_label' => $item->date_acquired_snapshot?->format('m/d/Y')
                        ?? $inventoryItem?->acquisition_date?->format('m/d/Y')
                        ?? '',
                    'property_number' => $this->nullableTrim($item->property_number_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->property_number)
                        ?? '',
                    'item_name' => $itemName,
                    'description_detail' => '',
                    'description' => $description,
                    'amount' => $item->amount_snapshot !== null ? (float) $item->amount_snapshot : null,
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
    private function buildDocumentMeta(Ptr $ptr, array $rows): array
    {
        return [
            'entity_name' => $this->nullableTrim($ptr->entity_name_snapshot)
                ?? config('print.entity_name')
                ?? config('app.lgu_name', config('app.name', 'Local Government Unit')),
            'appendix_label' => 'Appendix 76',
            'title' => 'Property Transfer Report',
            'fund_cluster' => $this->nullableTrim($ptr->header_fund_cluster_code_snapshot)
                ?? $this->fundClusterCode($ptr, 'from')
                ?? $this->fundClusterCode($ptr, 'to')
                ?? '',
            'ptr_number' => $this->nullableTrim($ptr->ptr_number) ?? '',
            'transfer_date_label' => $ptr->transfer_date?->format('m/d/Y') ?? '',
            'from_summary' => $this->buildAssignmentSummary(
                $this->nullableTrim($ptr->from_department_snapshot) ?? $this->officeSnapshot($ptr->fromDepartment),
                $this->nullableTrim($ptr->from_accountable_officer),
                $this->nullableTrim($ptr->from_fund_cluster_code_snapshot) ?? $this->fundClusterCode($ptr, 'from'),
            ),
            'to_summary' => $this->buildAssignmentSummary(
                $this->nullableTrim($ptr->to_department_snapshot) ?? $this->officeSnapshot($ptr->toDepartment),
                $this->nullableTrim($ptr->to_accountable_officer),
                $this->nullableTrim($ptr->to_fund_cluster_code_snapshot) ?? $this->fundClusterCode($ptr, 'to'),
            ),
            'transfer_type' => $this->nullableTrim($ptr->transfer_type) ?? '',
            'transfer_type_other' => $this->nullableTrim($ptr->transfer_type_other) ?? '',
            'reason_for_transfer' => $this->nullableTrim($ptr->reason_for_transfer) ?? '',
            'approved_by_name' => $this->nullableTrim($ptr->approved_by_name) ?? '',
            'approved_by_designation' => $this->nullableTrim($ptr->approved_by_designation) ?? '',
            'approved_by_date_label' => $ptr->approved_by_date?->format('m/d/Y') ?? '',
            'released_by_name' => $this->nullableTrim($ptr->released_by_name) ?? '',
            'released_by_designation' => $this->nullableTrim($ptr->released_by_designation) ?? '',
            'released_by_date_label' => $ptr->released_by_date?->format('m/d/Y') ?? '',
            'received_by_name' => $this->nullableTrim($ptr->received_by_name) ?? '',
            'received_by_designation' => $this->nullableTrim($ptr->received_by_designation) ?? '',
            'received_by_date_label' => $ptr->received_by_date?->format('m/d/Y') ?? '',
            'summary' => [
                'line_items' => count($rows),
                'printed_rows' => count($rows),
                'amount_total' => array_sum(array_map(
                    static fn (array $row): float => (float) ($row['amount'] ?? 0),
                    $rows,
                )),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvePaperProfile(?string $requestedPaper, array $paperOverrides): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_ptr', $requestedPaper);

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
        $descriptionCharsPerLine = max(1, (int) ($paperProfile['description_chars_per_line'] ?? 40));
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
        $propertyCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (18 / 39)));
        $conditionCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (15 / 39)));

        $descriptionUnits = $this->estimateCellUnits((string) ($row['description'] ?? ''), $descriptionCharsPerLine);
        $propertyUnits = $this->estimateCellUnits((string) ($row['property_number'] ?? ''), $propertyCharsPerLine);
        $conditionUnits = $this->estimateCellUnits((string) ($row['condition'] ?? ''), $conditionCharsPerLine);

        return max(1, $descriptionUnits, $propertyUnits, $conditionUnits);
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
    private function buildPtrSummary(Ptr $ptr): array
    {
        $label = $this->nullableTrim($ptr->ptr_number)
            ?? $this->nullableTrim($ptr->to_accountable_officer)
            ?? $this->nullableTrim($ptr->from_accountable_officer)
            ?? 'PTR Record';

        return [
            'id' => (string) $ptr->id,
            'label' => $label,
            'status' => (string) ($ptr->status ?? ''),
            'status_text' => strtoupper((string) ($ptr->status ?? 'draft')),
            'is_archived' => $ptr->trashed(),
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

    private function fundClusterCode(Ptr $ptr, string $side): ?string
    {
        $relation = $side === 'to' ? $ptr->toFundSource : $ptr->fromFundSource;
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
