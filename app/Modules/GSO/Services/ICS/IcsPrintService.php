<?php

namespace App\Modules\GSO\Services\ICS;

use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Models\IcsItem;
use App\Modules\GSO\Services\Contracts\ICS\IcsPrintServiceInterface;
use Illuminate\Support\Str;

class IcsPrintService implements IcsPrintServiceInterface
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

    public function buildReport(string $icsId, ?string $requestedPaper = null, array $paperOverrides = []): array
    {
        $ics = Ics::query()
            ->withTrashed()
            ->with([
                'department' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'fundSource' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name', 'fund_cluster_id']),
                'fundSource.fundCluster' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'items' => fn ($query) => $query
                    ->orderBy('line_no')
                    ->orderBy('created_at'),
                'items.inventoryItem' => fn ($query) => $query->withTrashed(),
                'items.inventoryItem.item' => fn ($query) => $query->withTrashed(),
            ])
            ->findOrFail($icsId);

        abort_if(
            (string) ($ics->status ?? '') !== 'finalized',
            409,
            'Only finalized ICS can be printed.'
        );

        $rows = $this->buildRows($ics);
        $paperProfile = $this->resolvePaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildPagination($rows, $paperProfile);
        $report = [
            'title' => 'Inventory Custodian Slip',
            'ics' => $this->buildIcsSummary($ics),
            'document' => $this->buildDocumentMeta($ics, $rows),
            'rows' => $rows,
            'pagination' => $pagination,
        ];

        return [
            'report' => $report,
            'paperProfile' => $paperProfile,
        ];
    }

    public function generatePdf(string $icsId, ?string $requestedPaper = null, array $paperOverrides = []): string
    {
        $payload = $this->buildReport($icsId, $requestedPaper, $paperOverrides);

        $filename = 'ics-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::ics.print.pdf',
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
    private function buildRows(Ics $ics): array
    {
        return $ics->items
            ->sortBy(fn (IcsItem $item) => [
                $item->line_no ?? 999999,
                (string) $item->created_at,
            ])
            ->values()
            ->map(function (IcsItem $item): array {
                $inventoryItem = $item->inventoryItem;
                $description = $this->nullableTrim($inventoryItem?->description)
                    ?? $this->nullableTrim($item->description_snapshot)
                    ?? $this->nullableTrim($item->item_name_snapshot)
                    ?? $this->nullableTrim($inventoryItem?->item?->item_name)
                    ?? '';

                return [
                    'quantity' => max(1, (int) ($item->quantity ?? 1)),
                    'unit' => $this->nullableTrim($item->unit_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->unit)
                        ?? '',
                    'unit_cost' => $item->unit_cost_snapshot !== null ? (float) $item->unit_cost_snapshot : null,
                    'total_cost' => $item->total_cost_snapshot !== null ? (float) $item->total_cost_snapshot : null,
                    'description' => $description,
                    'inventory_item_no' => $this->nullableTrim($item->inventory_item_no_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->inventory_item_no)
                        ?? '',
                    'estimated_useful_life' => $this->nullableTrim($item->estimated_useful_life_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->estimated_useful_life)
                        ?? '',
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function buildDocumentMeta(Ics $ics, array $rows): array
    {
        return [
            'entity_name' => $this->nullableTrim($ics->entity_name_snapshot)
                ?: config('print.entity_name')
                ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
            'appendix_label' => 'Appendix 59',
            'title' => 'Inventory Custodian Slip',
            'fund_cluster' => $this->fundClusterLabel($ics),
            'ics_no' => $this->nullableTrim($ics->ics_number) ?? '',
            'issued_date' => $ics->issued_date?->toDateString(),
            'issued_date_label' => $ics->issued_date?->format('m/d/Y') ?? '',
            'received_from_name' => $this->nullableTrim($ics->received_from_name) ?? '',
            'received_from_position' => $this->nullableTrim($ics->received_from_position) ?? '',
            'received_from_office' => $this->nullableTrim($ics->received_from_office) ?? '',
            'received_from_date_label' => $ics->received_from_date?->format('m/d/Y') ?? '',
            'received_by_name' => $this->nullableTrim($ics->received_by_name) ?? '',
            'received_by_position' => $this->nullableTrim($ics->received_by_position) ?? '',
            'received_by_office' => $this->nullableTrim($ics->received_by_office) ?? '',
            'received_by_date_label' => $ics->received_by_date?->format('m/d/Y') ?? '',
            'remarks' => $this->nullableTrim($ics->remarks) ?? '',
            'summary' => [
                'line_items' => count($rows),
                'printed_rows' => count($rows),
                'quantity_total' => array_sum(array_map(
                    static fn (array $row): int => (int) ($row['quantity'] ?? 0),
                    $rows,
                )),
                'amount_total' => array_sum(array_map(
                    static fn (array $row): float => (float) ($row['total_cost'] ?? 0),
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
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_ics', $requestedPaper);

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
        $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 28));
        $firstPageRows = max(1, (int) ($paperProfile['first_page_rows'] ?? $rowsPerPage));
        $laterPageRows = max(1, (int) ($paperProfile['later_page_rows'] ?? $rowsPerPage));
        $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? $rowsPerPage));
        $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
        $descriptionCharsPerLine = max(1, (int) ($paperProfile['description_chars_per_line'] ?? 42));
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
        $inventoryItemCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (18 / 34)));
        $usefulLifeCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (12 / 34)));

        $descriptionUnits = $this->estimateCellUnits((string) ($row['description'] ?? ''), $descriptionCharsPerLine);
        $inventoryUnits = $this->estimateCellUnits((string) ($row['inventory_item_no'] ?? ''), $inventoryItemCharsPerLine);
        $usefulLifeUnits = $this->estimateCellUnits((string) ($row['estimated_useful_life'] ?? ''), $usefulLifeCharsPerLine);

        return max(1, $descriptionUnits, $inventoryUnits, $usefulLifeUnits);
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
    private function buildIcsSummary(Ics $ics): array
    {
        $label = $this->nullableTrim($ics->ics_number)
            ?? $this->nullableTrim($ics->received_by_name)
            ?? 'ICS Record';

        return [
            'id' => (string) $ics->id,
            'label' => $label,
            'status' => (string) ($ics->status ?? ''),
            'status_text' => strtoupper((string) ($ics->status ?? 'draft')),
            'is_archived' => $ics->trashed(),
        ];
    }

    private function fundClusterLabel(Ics $ics): string
    {
        $snapshotCode = $this->nullableTrim($ics->fund_cluster_code_snapshot);
        $snapshotName = $this->nullableTrim($ics->fund_cluster_name_snapshot);
        $relatedCode = $this->nullableTrim($ics->fundSource?->fundCluster?->code);
        $relatedName = $this->nullableTrim($ics->fundSource?->fundCluster?->name);

        if ($snapshotCode !== null && $snapshotName !== null) {
            return "{$snapshotCode} - {$snapshotName}";
        }

        if ($snapshotCode !== null) {
            return $snapshotCode;
        }

        if ($snapshotName !== null) {
            return $snapshotName;
        }

        if ($relatedCode !== null && $relatedName !== null) {
            return "{$relatedCode} - {$relatedName}";
        }

        return $relatedCode ?? $relatedName ?? '';
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
