<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Services\Contracts\RpcspReportServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RpcspReportService extends AbstractPhysicalCountReportService implements RpcspReportServiceInterface
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
    ) {}

    public function getPrintViewData(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $asOf = null,
        bool $prefillCount = false,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = [],
    ): array {
        $legacy = parent::getPrintViewData(
            fundSourceId: $fundSourceId,
            departmentId: $departmentId,
            accountableOfficerId: $accountableOfficerId,
            asOf: $asOf,
            prefillCount: $prefillCount,
            signatories: $signatories,
        );

        $paperProfile = $this->resolveRpcspPaperProfile($requestedPaper, $paperOverrides);
        $rows = $legacy['rows'] ?? [];
        $pagination = $this->buildRpcspPagination($rows, $paperProfile);
        $document = $legacy['report'] ?? [];

        return [
            'report' => [
                'title' => 'Report on the Physical Count of Semi-Expendable Property',
                'document' => [
                    ...$document,
                    'summary' => $document['summary'] ?? [],
                    'signatories' => $document['signatories'] ?? [],
                ],
                'rows' => $rows,
                'pagination' => $pagination,
            ],
            'paperProfile' => $paperProfile,
            'available_funds' => $legacy['available_funds'] ?? [],
            'available_departments' => $legacy['available_departments'] ?? [],
            'available_accountable_officers' => $legacy['available_accountable_officers'] ?? [],
        ];
    }

    public function generatePdf(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $asOf = null,
        bool $prefillCount = false,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = [],
    ): string {
        $payload = $this->getPrintViewData(
            fundSourceId: $fundSourceId,
            departmentId: $departmentId,
            accountableOfficerId: $accountableOfficerId,
            asOf: $asOf,
            prefillCount: $prefillCount,
            signatories: $signatories,
            requestedPaper: $requestedPaper,
            paperOverrides: $paperOverrides,
        );

        $filename = 'rpcsp-report-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::reports.rpcsp.print.pdf',
            data: [
                'report' => $payload['report'],
                'paperProfile' => $payload['paperProfile'],
            ],
            outputPath: $outputPath,
        );
    }

    protected function reportCode(): string
    {
        return 'RPCSP';
    }

    protected function itemFallbackLabel(): string
    {
        return 'Semi-Expendable Property';
    }

    protected function applyClassificationScope(Builder $query): Builder
    {
        return $query->where('is_ics', true);
    }

    protected function matchesInventoryItem(InventoryItem $inventoryItem): bool
    {
        return (bool) ($inventoryItem->is_ics ?? false) === true;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $paperProfile
     * @return array<string, mixed>
     */
    private function buildRpcspPagination(array $rows, array $paperProfile): array
    {
        $rowsPerPage = max(1, (int) ($paperProfile['rows_per_page'] ?? 13));
        $gridRows = max($rowsPerPage, (int) ($paperProfile['grid_rows'] ?? $rowsPerPage));
        $lastPageGridRows = max(0, (int) ($paperProfile['last_page_grid_rows'] ?? 0));
        $descriptionCharsPerLine = max(1, (int) ($paperProfile['description_chars_per_line'] ?? 48));
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
                    $rowUnits = $this->estimateRpcspRowUnits($row, $descriptionCharsPerLine);

                    if ($pageRows !== [] && ($usedUnits + $rowUnits) > $rowsPerPage) {
                        break;
                    }

                    $pageRows[] = $row;
                    $usedUnits += $rowUnits;
                    $cursor++;
                }

                if ($pageRows === []) {
                    $pageRows[] = $rows[$cursor];
                    $usedUnits = $this->estimateRpcspRowUnits($rows[$cursor], $descriptionCharsPerLine);
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
    private function estimateRpcspRowUnits(array $row, int $descriptionCharsPerLine): int
    {
        $maxUnits = max(
            $this->estimateRpcspCellUnits($row['article'] ?? '', max(12, (int) round($descriptionCharsPerLine * 0.55))),
            $this->estimateRpcspCellUnits($row['description'] ?? '', $descriptionCharsPerLine),
            $this->estimateRpcspCellUnits($row['property_no'] ?? '', max(10, (int) round($descriptionCharsPerLine * 0.45))),
            $this->estimateRpcspCellUnits($row['remarks'] ?? '', max(10, (int) round($descriptionCharsPerLine * 0.45))),
        );

        return max(1, $maxUnits);
    }

    private function estimateRpcspCellUnits(mixed $value, int $charsPerLine): int
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
    private function resolveRpcspPaperProfile(?string $requestedPaper, array $overrides): array
    {
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_rpcsp', $requestedPaper);

        foreach (self::PAPER_OVERRIDE_KEYS as $key) {
            if (array_key_exists($key, $overrides) && is_int($overrides[$key])) {
                $paperProfile[$key] = $overrides[$key];
            }
        }

        return $paperProfile;
    }
}
