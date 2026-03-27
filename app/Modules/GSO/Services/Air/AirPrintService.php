<?php

namespace App\Modules\GSO\Services\Air;

use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\AirItemUnit;
use App\Modules\GSO\Services\Contracts\Air\AirPrintServiceInterface;
use App\Modules\GSO\Support\Air\AirStatuses;
use Illuminate\Support\Str;

class AirPrintService implements AirPrintServiceInterface
{
    private const MAX_GRID_ROWS = 24;

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

    public function buildReport(string $airId, ?string $requestedPaper = null, array $paperOverrides = []): array
    {
        $air = Air::query()
            ->withTrashed()
            ->with([
                'department' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'fundSource' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'items' => fn ($query) => $query
                    ->orderBy('item_name_snapshot')
                    ->orderBy('created_at'),
                'items.item' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'item_name', 'item_identification']),
                'items.units' => fn ($query) => $query
                    ->orderBy('created_at')
                    ->orderBy('id'),
            ])
            ->findOrFail($airId);

        $rows = $this->buildRows($air);
        $paperProfile = $this->resolvePaperProfile($requestedPaper, $paperOverrides);
        $pagination = $this->buildPagination($rows, $paperProfile);
        $report = [
            'title' => 'Acceptance and Inspection Report',
            'air' => $this->buildAirSummary($air),
            'document' => $this->buildPrintMeta($air, $rows),
            'rows' => $rows,
            'max_grid_rows' => self::MAX_GRID_ROWS,
            'pagination' => $pagination,
            'can_open_inspection' => in_array(
                (string) ($air->status ?? ''),
                ['submitted', 'in_progress', 'inspected'],
                true,
            ),
        ];

        return [
            'report' => $report,
            'paperProfile' => $paperProfile,
        ];
    }

    public function generatePdf(string $airId, ?string $requestedPaper = null, array $paperOverrides = []): string
    {
        $payload = $this->buildReport($airId, $requestedPaper, $paperOverrides);

        $filename = 'air-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::air.print.pdf',
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
    private function buildRows(Air $air): array
    {
        $rows = [];

        foreach ($air->items as $airItem) {
            $description = $this->airItemDescription($airItem);
            $propertyNo = $this->airItemPropertyNo($airItem);
            $unitLabel = $this->nullableTrim($airItem->unit_snapshot) ?? '';
            $isUnitTracked = $this->requiresUnitTracking($airItem);
            $activeUnits = $airItem->units
                ->filter(fn (AirItemUnit $unit): bool => $unit->deleted_at === null)
                ->values();

            if ($isUnitTracked && $activeUnits->isNotEmpty()) {
                foreach ($activeUnits as $index => $unit) {
                    $rows[] = [
                        'property_no' => $propertyNo,
                        'description' => $description,
                        'unit' => $unitLabel,
                        'quantity' => 1,
                        'type' => 'unit',
                        'unit_label' => $this->buildUnitLabel($unit, $index + 1),
                    ];
                }

                continue;
            }

            $rows[] = [
                'property_no' => $propertyNo,
                'description' => $description,
                'unit' => $unitLabel,
                'quantity' => $this->displayQuantity($airItem),
                'type' => $isUnitTracked ? 'unit-summary' : 'line',
                'unit_label' => null,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function buildPrintMeta(Air $air, array $rows): array
    {
        $quantityTotal = array_sum(array_map(
            static fn (array $row): int => (int) ($row['quantity'] ?? 0),
            $rows,
        ));

        $unitRows = count(array_filter(
            $rows,
            static fn (array $row): bool => in_array((string) ($row['type'] ?? ''), ['unit', 'unit-summary'], true),
        ));

        return [
            'entity_name' => config('print.entity_name')
                ?: config('app.lgu_name', config('app.name', 'Local Government Unit')),
            'appendix_label' => 'Appendix 30',
            'title' => 'Acceptance and Inspection Report',
            'supplier' => $this->nullableTrim($air->supplier_name) ?? '',
            'air_no' => $this->nullableTrim($air->air_number) ?? '',
            'air_date' => $air->air_date?->toDateString(),
            'air_date_label' => $air->air_date?->format('m/d/Y') ?? '',
            'fund_source' => $this->fundSourceLabel($air),
            'office_department' => $this->departmentLabel($air),
            'po_number' => $this->nullableTrim($air->po_number) ?? '',
            'po_date' => $air->po_date?->toDateString(),
            'po_date_label' => $air->po_date?->format('m/d/Y') ?? '',
            'invoice_no' => $this->nullableTrim($air->invoice_number) ?? '',
            'invoice_date' => $air->invoice_date?->toDateString(),
            'invoice_date_label' => $air->invoice_date?->format('m/d/Y') ?? '',
            'date_received' => $air->date_received?->toDateString(),
            'date_received_label' => $air->date_received?->format('m/d/Y') ?? '',
            'received_completeness' => $this->nullableTrim($air->received_completeness) ?? '',
            'received_completeness_text' => $this->simpleLabel($air->received_completeness),
            'received_notes' => $this->nullableTrim($air->received_notes) ?? '',
            'date_inspected' => $air->date_inspected?->toDateString(),
            'date_inspected_label' => $air->date_inspected?->format('m/d/Y') ?? '',
            'inspection_verified' => $air->inspection_verified,
            'inspection_verified_text' => $air->inspection_verified === null
                ? 'Pending'
                : ($air->inspection_verified ? 'Verified' : 'Needs Review'),
            'accepted_by_name' => $this->nullableTrim($air->accepted_by_name) ?? '',
            'accepted_by_designation' => 'Supply Officer-Designate',
            'inspected_by_name' => $this->nullableTrim($air->inspected_by_name) ?? '',
            'inspected_by_designation' => 'Municipal Accountant',
            'summary' => [
                'line_items' => $air->items->count(),
                'printed_rows' => count($rows),
                'unit_rows' => $unitRows,
                'quantity_total' => $quantityTotal,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAirSummary(Air $air): array
    {
        return [
            'id' => (string) $air->id,
            'label' => $this->airLabel($air),
            'status' => (string) ($air->status ?? ''),
            'status_text' => AirStatuses::label((string) ($air->status ?? '')),
            'continuation_label' => $air->parent_air_id
                ? 'Follow-up #' . max(1, (int) ($air->continuation_no ?? 1))
                : 'Root AIR',
            'is_archived' => $air->trashed(),
        ];
    }

    private function airLabel(Air $air): string
    {
        $poNumber = $this->nullableTrim($air->po_number);
        $airNumber = $this->nullableTrim($air->air_number);

        if ($poNumber !== null && $airNumber !== null) {
            return "{$poNumber} / {$airNumber}";
        }

        return $poNumber ?? $airNumber ?? 'AIR Record';
    }

    private function airItemDescription(AirItem $airItem): string
    {
        return $this->nullableTrim($airItem->description_snapshot)
            ?? $this->nullableTrim($airItem->item_name_snapshot)
            ?? $this->nullableTrim($airItem->item?->item_name)
            ?? 'AIR Item';
    }

    private function airItemPropertyNo(AirItem $airItem): string
    {
        return $this->nullableTrim($airItem->stock_no_snapshot)
            ?? $this->nullableTrim($airItem->item?->item_identification)
            ?? '';
    }

    private function displayQuantity(AirItem $airItem): int
    {
        $accepted = max(0, (int) ($airItem->qty_accepted ?? 0));
        if ($accepted > 0) {
            return $accepted;
        }

        $delivered = max(0, (int) ($airItem->qty_delivered ?? 0));
        if ($delivered > 0) {
            return $delivered;
        }

        return max(0, (int) ($airItem->qty_ordered ?? 0));
    }

    private function requiresUnitTracking(AirItem $airItem): bool
    {
        $trackingType = strtolower(trim((string) ($airItem->tracking_type_snapshot ?? '')));

        return $trackingType === 'property'
            || (bool) ($airItem->requires_serial_snapshot ?? false)
            || (bool) ($airItem->is_semi_expendable_snapshot ?? false);
    }

    private function buildUnitLabel(AirItemUnit $unit, int $fallbackIndex): string
    {
        $serial = $this->nullableTrim($unit->serial_number);
        $propertyNumber = $this->nullableTrim($unit->property_number);
        $brand = $this->nullableTrim($unit->brand);
        $model = $this->nullableTrim($unit->model);

        if ($serial !== null) {
            return 'Serial: ' . $serial;
        }

        if ($propertyNumber !== null) {
            return 'Tagged: ' . $propertyNumber;
        }

        if ($brand !== null && $model !== null) {
            return "{$brand} {$model}";
        }

        if ($brand !== null) {
            return $brand;
        }

        if ($model !== null) {
            return $model;
        }

        return 'Unit #' . $fallbackIndex;
    }

    private function departmentLabel(Air $air): string
    {
        $snapshotCode = $this->nullableTrim($air->requesting_department_code_snapshot);
        $snapshotName = $this->nullableTrim($air->requesting_department_name_snapshot);
        $relatedCode = $this->nullableTrim($air->department?->code);
        $relatedName = $this->nullableTrim($air->department?->name);

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

    private function fundSourceLabel(Air $air): string
    {
        $code = $this->nullableTrim($air->fundSource?->code);
        $name = $this->nullableTrim($air->fundSource?->name);
        $fallback = $this->nullableTrim($air->fund);

        if ($code !== null && $name !== null) {
            return "{$code} - {$name}";
        }

        return $name ?? $code ?? $fallback ?? '';
    }

    private function simpleLabel(mixed $value): string
    {
        $value = $this->nullableTrim($value);

        return $value !== null ? ucwords(str_replace('_', ' ', $value)) : 'None';
    }

    /**
     * @param  array<string, mixed>  $paperOverrides
     * @return array<string, mixed>
     */
    private function resolvePaperProfile(?string $requestedPaper, array $paperOverrides): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_air', $requestedPaper);

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
        $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 22));
        $firstPageRows = max(1, (int) ($paperProfile['first_page_rows'] ?? $rowsPerPage));
        $laterPageRows = max(1, (int) ($paperProfile['later_page_rows'] ?? $rowsPerPage));
        $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? self::MAX_GRID_ROWS));
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
        $description = str_replace(["\r\n", "\r"], "\n", (string) ($row['description'] ?? ''));
        $segments = explode("\n", $description);
        $units = 0;

        foreach ($segments as $segment) {
            $text = preg_replace('/\s+/u', ' ', trim($segment)) ?? '';
            $units += max(1, (int) ceil(mb_strlen($text) / $descriptionCharsPerLine));
        }

        return max(1, $units);
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
