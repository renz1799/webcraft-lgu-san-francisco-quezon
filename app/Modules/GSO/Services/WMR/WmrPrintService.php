<?php

namespace App\Modules\GSO\Services\WMR;

use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Models\WmrItem;
use App\Modules\GSO\Services\Contracts\WMR\WmrPrintServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class WmrPrintService implements WmrPrintServiceInterface
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

    public function buildReport(string $wmrId, ?string $requestedPaper = null, array $paperOverrides = []): array
    {
        $wmr = Wmr::query()
            ->withTrashed()
            ->with([
                'fundCluster' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'items' => fn ($query) => $query
                    ->orderBy('line_no')
                    ->orderBy('created_at'),
                'items.inventoryItem' => fn ($query) => $query->withTrashed(),
                'items.inventoryItem.item' => fn ($query) => $query->withTrashed(),
            ])
            ->findOrFail($wmrId);

        $rows = $this->buildRows($wmr);
        $paperProfile = $this->resolvePaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildPagination($rows, $paperProfile);
        $report = [
            'title' => 'Waste Materials Report',
            'wmr' => $this->buildWmrSummary($wmr),
            'document' => $this->buildDocumentMeta($wmr, $rows),
            'rows' => $rows,
            'pagination' => $pagination,
        ];

        return [
            'report' => $report,
            'paperProfile' => $paperProfile,
        ];
    }

    public function generatePdf(string $wmrId, ?string $requestedPaper = null, array $paperOverrides = []): string
    {
        $payload = $this->buildReport($wmrId, $requestedPaper, $paperOverrides);

        $filename = 'wmr-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::wmrs.print.pdf',
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
    private function buildRows(Wmr $wmr): array
    {
        return $wmr->items
            ->sortBy(fn (WmrItem $item) => [
                (int) ($item->line_no ?? 999999),
                (string) $item->created_at,
            ])
            ->values()
            ->map(function (WmrItem $item): array {
                $inventoryItem = $item->inventoryItem;
                $itemName = $this->nullableTrim($inventoryItem?->item?->item_name)
                    ?? $this->nullableTrim($item->item_name_snapshot)
                    ?? '';
                $description = $this->nullableTrim($inventoryItem?->description)
                    ?? $this->nullableTrim($item->description_snapshot)
                    ?? $itemName;
                $descriptionDetail = '';

                if ($description !== '' && mb_strtolower($description) !== mb_strtolower($itemName)) {
                    $descriptionDetail = $description;
                }

                $printDescription = trim(implode("\n", array_filter([
                    $itemName,
                    $descriptionDetail,
                ], static fn (?string $value): bool => trim((string) $value) !== '')));

                if ($printDescription === '') {
                    $printDescription = $description;
                }

                return [
                    'line_no' => (int) ($item->line_no ?? 0) > 0 ? (int) $item->line_no : 0,
                    'quantity' => max(1, (int) ($item->quantity ?? 1)),
                    'unit' => $this->nullableTrim($item->unit_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->unit)
                        ?? '',
                    'item_name' => $itemName,
                    'description_detail' => $descriptionDetail,
                    'print_description' => $printDescription,
                    'receipt_no' => $this->nullableTrim($item->official_receipt_no) ?? '',
                    'receipt_date_label' => $item->official_receipt_date?->format('m/d/Y') ?? '',
                    'amount' => $item->official_receipt_amount !== null ? (float) $item->official_receipt_amount : null,
                    'disposal_method' => $this->nullableTrim($item->disposal_method) ?? '',
                    'transfer_entity_name' => $this->nullableTrim($item->transfer_entity_name) ?? '',
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function buildDocumentMeta(Wmr $wmr, array $rows): array
    {
        $rowCollection = collect($rows);

        return [
            'title' => 'Waste Materials Report',
            'appendix_label' => 'Appendix 65',
            'entity_name' => $this->entityName($wmr),
            'fund_cluster' => $this->nullableTrim($wmr->fund_cluster_code_snapshot)
                ?? $this->nullableTrim($wmr->fundCluster?->code)
                ?? '',
            'place_of_storage' => $this->nullableTrim($wmr->place_of_storage) ?? '',
            'report_date_label' => $wmr->report_date?->format('m/d/Y') ?? '',
            'wmr_number' => $this->nullableTrim($wmr->wmr_number) ?? '',
            'custodian_name' => $this->nullableTrim($wmr->custodian_name) ?? '',
            'custodian_designation' => $this->nullableTrim($wmr->custodian_designation) ?? '',
            'approved_by_name' => $this->nullableTrim($wmr->approved_by_name) ?? '',
            'approved_by_designation' => $this->nullableTrim($wmr->approved_by_designation) ?? '',
            'inspection_officer_name' => $this->nullableTrim($wmr->inspection_officer_name) ?? '',
            'inspection_officer_designation' => $this->nullableTrim($wmr->inspection_officer_designation) ?? '',
            'witness_name' => $this->nullableTrim($wmr->witness_name) ?? '',
            'witness_designation' => $this->nullableTrim($wmr->witness_designation) ?? '',
            'destroyed_lines' => $this->lineNumbersForMethod($rowCollection, 'destroyed'),
            'private_sale_lines' => $this->lineNumbersForMethod($rowCollection, 'private_sale'),
            'public_auction_lines' => $this->lineNumbersForMethod($rowCollection, 'public_auction'),
            'transfer_summary' => $this->transferSummary($rowCollection),
            'summary' => [
                'line_items' => $rowCollection->count(),
                'printed_rows' => $rowCollection->count(),
                'amount_total' => $rowCollection->sum(fn (array $row): float => (float) ($row['amount'] ?? 0)),
                'destroyed_count' => $rowCollection->where('disposal_method', 'destroyed')->count(),
                'sale_count' => $rowCollection
                    ->filter(fn (array $row): bool => in_array((string) ($row['disposal_method'] ?? ''), ['private_sale', 'public_auction'], true))
                    ->count(),
                'transfer_count' => $rowCollection->where('disposal_method', 'transferred_without_cost')->count(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolvePaperProfile(?string $requestedPaper, array $paperOverrides): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_wmr', $requestedPaper);

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
        $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 10));
        $firstPageRows = max(1, (int) ($paperProfile['first_page_rows'] ?? $rowsPerPage));
        $laterPageRows = max(1, (int) ($paperProfile['later_page_rows'] ?? $rowsPerPage));
        $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? $rowsPerPage));
        $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
        $descriptionCharsPerLine = max(1, (int) ($paperProfile['description_chars_per_line'] ?? 36));
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
        $receiptCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (14 / 28)));

        $descriptionUnits = $this->estimateCellUnits((string) ($row['print_description'] ?? ''), $descriptionCharsPerLine);
        $receiptUnits = $this->estimateCellUnits((string) ($row['receipt_no'] ?? ''), $receiptCharsPerLine);

        return max(1, $descriptionUnits, $receiptUnits);
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
    private function buildWmrSummary(Wmr $wmr): array
    {
        $label = $this->nullableTrim($wmr->wmr_number)
            ?? $this->nullableTrim($wmr->place_of_storage)
            ?? 'WMR Record';

        return [
            'id' => (string) $wmr->id,
            'label' => $label,
            'status' => (string) ($wmr->status ?? ''),
            'status_text' => strtoupper((string) ($wmr->status ?? 'draft')),
            'is_archived' => $wmr->trashed(),
        ];
    }

    private function entityName(Wmr $wmr): string
    {
        return $this->nullableTrim($wmr->entity_name_snapshot)
            ?? $this->nullableTrim((string) config('print.entity_name'))
            ?? $this->nullableTrim((string) config('app.lgu_name'))
            ?? $this->nullableTrim((string) config('app.name'))
            ?? 'Local Government Unit';
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    private function lineNumbersForMethod(Collection $rows, string $method): string
    {
        return $rows
            ->filter(fn (array $row): bool => (string) ($row['disposal_method'] ?? '') === $method)
            ->map(fn (array $row): string => (int) ($row['line_no'] ?? 0) > 0 ? (string) $row['line_no'] : '')
            ->filter(fn (string $value): bool => $value !== '')
            ->implode(', ');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    private function transferSummary(Collection $rows): string
    {
        return $rows
            ->filter(fn (array $row): bool => (string) ($row['disposal_method'] ?? '') === 'transferred_without_cost')
            ->map(function (array $row): string {
                $lineNo = (int) ($row['line_no'] ?? 0) > 0 ? (string) $row['line_no'] : '?';
                $entity = $this->nullableTrim($row['transfer_entity_name'] ?? null) ?? 'Receiving agency/entity';

                return $lineNo . ' to ' . $entity;
            })
            ->implode('; ');
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
