<?php

namespace App\Modules\GSO\Services\PAR;

use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Models\ParItem;
use App\Modules\GSO\Services\Contracts\PAR\ParPrintServiceInterface;
use Illuminate\Support\Str;

class ParPrintService implements ParPrintServiceInterface
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

    public function buildReport(string $parId, ?string $requestedPaper = null, array $paperOverrides = []): array
    {
        $par = Par::query()
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
                    ->orderBy('property_number_snapshot')
                    ->orderBy('created_at'),
                'items.inventoryItem' => fn ($query) => $query->withTrashed(),
                'items.inventoryItem.item' => fn ($query) => $query->withTrashed(),
            ])
            ->findOrFail($parId);

        $rows = $this->buildRows($par);
        $paperProfile = $this->resolvePaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildPagination($rows, $paperProfile);
        $report = [
            'title' => 'Property Acknowledgement Receipt',
            'par' => $this->buildParSummary($par),
            'document' => $this->buildDocumentMeta($par, $rows),
            'rows' => $rows,
            'pagination' => $pagination,
        ];

        return [
            'report' => $report,
            'paperProfile' => $paperProfile,
        ];
    }

    public function generatePdf(string $parId, ?string $requestedPaper = null, array $paperOverrides = []): string
    {
        $payload = $this->buildReport($parId, $requestedPaper, $paperOverrides);

        $filename = 'par-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::pars.print.pdf',
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
    private function buildRows(Par $par): array
    {
        return $par->items
            ->sortBy(fn (ParItem $item) => [
                $this->nullableTrim($item->property_number_snapshot)
                    ?? $this->nullableTrim($item->inventoryItem?->property_number)
                    ?? 'ZZZ',
                (string) $item->created_at,
            ])
            ->values()
            ->map(function (ParItem $item): array {
                $inventoryItem = $item->inventoryItem;

                return [
                    'quantity' => max(1, (int) ($item->quantity ?? 1)),
                    'unit' => $this->nullableTrim($item->unit_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->unit)
                        ?? '',
                    'description' => $this->nullableTrim($inventoryItem?->description)
                        ?? $this->nullableTrim($item->item_name_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->item?->item_name)
                        ?? '',
                    'property_number' => $this->nullableTrim($item->property_number_snapshot)
                        ?? $this->nullableTrim($inventoryItem?->property_number)
                        ?? '',
                    'date_acquired_label' => $inventoryItem?->acquisition_date?->format('m/d/Y') ?? '',
                    'amount' => $item->amount_snapshot !== null ? (float) $item->amount_snapshot : null,
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function buildDocumentMeta(Par $par, array $rows): array
    {
        return [
            'entity_name' => config('print.entity_name')
                ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
            'appendix_label' => 'Appendix 71',
            'title' => 'Property Acknowledgement Receipt',
            'issued_date' => $par->issued_date?->toDateString(),
            'issued_date_label' => $par->issued_date?->format('m/d/Y') ?? '',
            'par_number' => $this->nullableTrim($par->par_number) ?? '',
            'fund_cluster' => $this->fundClusterLabel($par),
            'department' => $this->departmentLabel($par),
            'remarks' => $this->nullableTrim($par->remarks) ?? '',
            'received_by_name' => $this->nullableTrim($par->person_accountable) ?? '',
            'received_by_position' => $this->nullableTrim($par->received_by_position) ?? '',
            'received_by_date_label' => $par->received_by_date?->format('m/d/Y') ?? '',
            'issued_by_name' => $this->nullableTrim($par->issued_by_name) ?? '',
            'issued_by_position' => $this->nullableTrim($par->issued_by_position) ?? '',
            'issued_by_office' => $this->nullableTrim($par->issued_by_office) ?? '',
            'issued_by_date_label' => $par->issued_by_date?->format('m/d/Y') ?? '',
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
     * @return array<string, mixed>
     */
    private function resolvePaperProfile(?string $requestedPaper, array $paperOverrides): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_par', $requestedPaper);

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
        $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 26));
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
        $propertyCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (20 / 35)));
        $unitCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (8 / 35)));

        $descriptionUnits = $this->estimateCellUnits((string) ($row['description'] ?? ''), $descriptionCharsPerLine);
        $propertyUnits = $this->estimateCellUnits((string) ($row['property_number'] ?? ''), $propertyCharsPerLine);
        $unitUnits = $this->estimateCellUnits((string) ($row['unit'] ?? ''), $unitCharsPerLine);

        return max(1, $descriptionUnits, $propertyUnits, $unitUnits);
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
    private function buildParSummary(Par $par): array
    {
        $label = $this->nullableTrim($par->par_number)
            ?? $this->nullableTrim($par->person_accountable)
            ?? 'PAR Record';

        return [
            'id' => (string) $par->id,
            'label' => $label,
            'status' => (string) ($par->status ?? ''),
            'status_text' => strtoupper((string) ($par->status ?? 'draft')),
            'is_archived' => $par->trashed(),
        ];
    }

    private function departmentLabel(Par $par): string
    {
        $code = $this->nullableTrim($par->department?->code);
        $name = $this->nullableTrim($par->department?->name);

        if ($code !== null && $name !== null) {
            return "{$code} - {$name}";
        }

        return $name ?? $code ?? '';
    }

    private function fundClusterLabel(Par $par): string
    {
        $code = $this->nullableTrim($par->fundSource?->fundCluster?->code);
        $name = $this->nullableTrim($par->fundSource?->fundCluster?->name);

        if ($code !== null && $name !== null) {
            return "{$code} - {$name}";
        }

        return $name ?? $code ?? '';
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
