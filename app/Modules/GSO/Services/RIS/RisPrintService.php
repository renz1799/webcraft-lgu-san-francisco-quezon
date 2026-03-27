<?php

namespace App\Modules\GSO\Services\RIS;

use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Services\Contracts\RIS\RisPrintServiceInterface;
use Illuminate\Support\Str;

class RisPrintService implements RisPrintServiceInterface
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

    public function buildReport(string $risId, ?string $requestedPaper = null, array $paperOverrides = []): array
    {
        $ris = Ris::query()
            ->withTrashed()
            ->with([
                'department' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'fundSource' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'items' => fn ($query) => $query
                    ->orderBy('line_no')
                    ->orderBy('created_at'),
            ])
            ->findOrFail($risId);

        $rows = $this->buildRows($ris);
        $paperProfile = $this->resolvePaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildPagination($rows, $paperProfile);
        $report = [
            'title' => 'Requisition and Issue Slip',
            'ris' => $this->buildRisSummary($ris),
            'document' => $this->buildDocumentMeta($ris, $rows),
            'rows' => $rows,
            'pagination' => $pagination,
        ];

        return [
            'report' => $report,
            'paperProfile' => $paperProfile,
        ];
    }

    public function generatePdf(string $risId, ?string $requestedPaper = null, array $paperOverrides = []): string
    {
        $payload = $this->buildReport($risId, $requestedPaper, $paperOverrides);

        $filename = 'ris-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::ris.print.pdf',
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
    private function buildRows(Ris $ris): array
    {
        return $ris->items
            ->sortBy(fn ($item) => $item->line_no ?? 999999)
            ->values()
            ->map(fn ($item) => [
                'stock_no' => $this->nullableTrim($item->stock_no_snapshot) ?? '',
                'unit' => $this->nullableTrim($item->unit_snapshot) ?? '',
                'description' => $this->nullableTrim($item->description_snapshot)
                    ?? $this->nullableTrim($item->item_name_snapshot)
                    ?? '',
                'qty_requested' => max(0, (int) ($item->qty_requested ?? 0)),
                'qty_issued' => max(0, (int) ($item->qty_issued ?? 0)),
                'remarks' => $this->nullableTrim($item->remarks) ?? '',
            ])
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function buildDocumentMeta(Ris $ris, array $rows): array
    {
        return [
            'entity_name' => config('print.entity_name')
                ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
            'appendix_label' => 'Appendix 48',
            'title' => 'Requisition and Issue Slip',
            'ris_no' => $this->nullableTrim($ris->ris_number) ?? '',
            'ris_date' => $ris->ris_date?->toDateString(),
            'ris_date_label' => $ris->ris_date?->format('m/d/Y') ?? '',
            'office' => $this->departmentLabel($ris),
            'fund' => $this->fundLabel($ris),
            'division' => $this->nullableTrim($ris->division) ?? '',
            'fpp_code' => $this->nullableTrim($ris->fpp_code) ?? '',
            'responsibility_center_code' => $this->nullableTrim($ris->responsibility_center_code) ?? '',
            'purpose' => $this->nullableTrim($ris->purpose) ?? '',
            'remarks' => $this->nullableTrim($ris->remarks) ?? '',
            'requested_by_name' => $this->nullableTrim($ris->requested_by_name) ?? '',
            'requested_by_designation' => $this->nullableTrim($ris->requested_by_designation) ?? '',
            'requested_by_date_label' => $ris->requested_by_date?->format('m/d/Y') ?? '',
            'approved_by_name' => $this->nullableTrim($ris->approved_by_name) ?? '',
            'approved_by_designation' => $this->nullableTrim($ris->approved_by_designation) ?? '',
            'approved_by_date_label' => $ris->approved_by_date?->format('m/d/Y') ?? '',
            'issued_by_name' => $this->nullableTrim($ris->issued_by_name) ?? '',
            'issued_by_designation' => $this->nullableTrim($ris->issued_by_designation) ?? '',
            'issued_by_date_label' => $ris->issued_by_date?->format('m/d/Y') ?? '',
            'received_by_name' => $this->nullableTrim($ris->received_by_name) ?? '',
            'received_by_designation' => $this->nullableTrim($ris->received_by_designation) ?? '',
            'received_by_date_label' => $ris->received_by_date?->format('m/d/Y') ?? '',
            'summary' => [
                'line_items' => count($rows),
                'printed_rows' => count($rows),
                'qty_requested_total' => array_sum(array_map(
                    static fn (array $row): int => (int) ($row['qty_requested'] ?? 0),
                    $rows,
                )),
                'qty_issued_total' => array_sum(array_map(
                    static fn (array $row): int => (int) ($row['qty_issued'] ?? 0),
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
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_ris', $requestedPaper);

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
        $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 24));
        $firstPageRows = max(1, (int) ($paperProfile['first_page_rows'] ?? $rowsPerPage));
        $laterPageRows = max(1, (int) ($paperProfile['later_page_rows'] ?? $rowsPerPage));
        $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? $rowsPerPage));
        $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
        $descriptionCharsPerLine = max(1, (int) ($paperProfile['description_chars_per_line'] ?? 52));
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
        $remarksCharsPerLine = max(8, (int) floor($descriptionCharsPerLine * (14 / 43)));
        $descriptionUnits = $this->estimateCellUnits((string) ($row['description'] ?? ''), $descriptionCharsPerLine);
        $remarksUnits = $this->estimateCellUnits((string) ($row['remarks'] ?? ''), $remarksCharsPerLine);

        return max(1, $descriptionUnits, $remarksUnits);
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
    private function buildRisSummary(Ris $ris): array
    {
        $label = $this->nullableTrim($ris->ris_number)
            ?? $this->nullableTrim($ris->requesting_department_name_snapshot)
            ?? 'RIS Record';

        return [
            'id' => (string) $ris->id,
            'label' => $label,
            'status' => (string) ($ris->status ?? ''),
            'status_text' => strtoupper((string) ($ris->status ?? 'draft')),
            'is_archived' => $ris->trashed(),
        ];
    }

    private function departmentLabel(Ris $ris): string
    {
        $snapshotCode = $this->nullableTrim($ris->requesting_department_code_snapshot);
        $snapshotName = $this->nullableTrim($ris->requesting_department_name_snapshot);
        $relatedCode = $this->nullableTrim($ris->department?->code);
        $relatedName = $this->nullableTrim($ris->department?->name);

        if ($snapshotCode !== null && $snapshotName !== null) {
            return "{$snapshotCode} - {$snapshotName}";
        }

        if ($snapshotName !== null) {
            return $snapshotName;
        }

        if ($relatedCode !== null && $relatedName !== null) {
            return "{$relatedCode} - {$relatedName}";
        }

        return $relatedCode ?? $relatedName ?? '';
    }

    private function fundLabel(Ris $ris): string
    {
        $code = $this->nullableTrim($ris->fundSource?->code);
        $name = $this->nullableTrim($ris->fundSource?->name);
        $fallback = $this->nullableTrim($ris->fund);

        if ($code !== null && $name !== null) {
            return "{$code} - {$name}";
        }

        return $name ?? $code ?? $fallback ?? '';
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
