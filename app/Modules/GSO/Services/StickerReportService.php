<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Services\Contracts\StickerReportServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StickerReportService implements StickerReportServiceInterface
{
    private const STICKERS_PER_ROW = 2;
    private const STICKERS_PER_PAGE = 8;
    private const PAGE_PROGRESS_START = 8;
    private const PAGE_PROGRESS_END = 88;
    private const FINALIZE_PROGRESS = 94;

    public function __construct(
        private readonly PdfGeneratorInterface $pdfGenerator,
    ) {}

    public function getPrintViewData(array $filters = []): array
    {
        $copies = max(1, min((int) ($filters['copies'] ?? 2), 24));
        $showCutGuides = array_key_exists('show_cut_guides', $filters)
            ? (bool) $filters['show_cut_guides']
            : true;

        $selectedInventoryItemIds = $this->normalizeInventoryItemIds($filters);
        $selectedInventoryItems = $this->selectedInventoryItems($selectedInventoryItemIds);
        $selectedStickers = $selectedInventoryItems
            ->map(fn (InventoryItem $inventoryItem): array => $this->buildSticker($inventoryItem))
            ->values();
        $selectedSticker = $selectedStickers->first();
        $stickers = $selectedStickers
            ->flatMap(fn (array $sticker): Collection => $this->buildStickerCopies($sticker, $copies))
            ->values()
            ->all();
        $classificationLabels = $selectedStickers->pluck('type_label')->unique()->values();

        $pageCount = $selectedStickers->isNotEmpty()
            ? (int) ceil(max(count($stickers), 1) / self::STICKERS_PER_PAGE)
            : 0;

        return [
            'report' => [
                'title' => 'Sticker Printing',
                'summary' => [
                    'selected_asset' => $selectedInventoryItems->count() === 1
                        ? $this->inventoryItemOptionLabel($selectedInventoryItems->first())
                        : ($selectedInventoryItems->isNotEmpty()
                            ? number_format($selectedInventoryItems->count()) . ' assets selected'
                            : 'No asset selected'),
                    'selected_assets_count' => $selectedInventoryItems->count(),
                    'classification' => $classificationLabels->count() === 1
                        ? ($classificationLabels->first() ?? '-')
                        : ($classificationLabels->isNotEmpty() ? 'Mixed' : '-'),
                    'copies' => $selectedStickers->isNotEmpty() ? $copies : 0,
                    'total_stickers' => count($stickers),
                    'page_count' => $pageCount,
                    'stickers_per_page' => self::STICKERS_PER_PAGE,
                ],
            ],
            'selectedInventoryItem' => $selectedInventoryItems->first(),
            'selectedInventoryItems' => $selectedInventoryItems,
            'selectedStickers' => $selectedStickers->all(),
            'availableInventoryItems' => $this->availableInventoryItems($selectedInventoryItems),
            'sticker' => $selectedSticker,
            'stickers' => $stickers,
            'controls' => [
                'copies' => $copies,
                'show_cut_guides' => $showCutGuides,
            ],
            'sheet' => [
                'stickers_per_row' => self::STICKERS_PER_ROW,
                'stickers_per_page' => self::STICKERS_PER_PAGE,
                'page_count' => $pageCount,
            ],
        ];
    }

    public function generatePdf(array $filters = []): string
    {
        return $this->generatePdfWithProgress($filters);
    }

    public function generatePdfWithProgress(array $filters = [], ?callable $progress = null, ?string $outputPath = null): string
    {
        $payload = $this->getPrintViewData($filters);
        $filename = basename($outputPath ?: ('stickers-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf'));
        $finalOutputPath = $outputPath ?: storage_path('app/tmp/' . $filename);
        $buildDirectory = dirname($finalOutputPath) . DIRECTORY_SEPARATOR . 'build-' . Str::uuid();
        $htmlPath = $buildDirectory . DIRECTORY_SEPARATOR . 'document.html';
        $pages = collect($payload['stickers'] ?? [])->chunk(self::STICKERS_PER_PAGE)->values();
        $totalPages = max(
            1,
            $pages->count(),
            (int) ($payload['sheet']['page_count'] ?? 0),
        );
        $stickerBackgroundUrl = $this->localFileUrl(public_path('print/sticker.jpg'));

        File::ensureDirectoryExists($buildDirectory);

        try {
            $this->reportProgress($progress, [
                'status' => 'running',
                'stage' => 'Preparing sticker pages...',
                'progress_percent' => self::PAGE_PROGRESS_START,
                'total_pages' => $totalPages,
                'completed_pages' => 0,
            ]);

            File::put($htmlPath, $this->renderPdfDocumentStart(
                $payload['report'],
                $payload['sticker'],
                $stickerBackgroundUrl,
            ));

            if ($pages->isEmpty()) {
                File::append($htmlPath, view('gso::reports.stickers.print.partials.empty-page')->render());

                $this->reportProgress($progress, [
                    'status' => 'running',
                    'stage' => 'Prepared the sticker sheet page.',
                    'progress_percent' => self::PAGE_PROGRESS_END,
                    'total_pages' => $totalPages,
                    'completed_pages' => 1,
                ]);
            } else {
                foreach ($pages as $pageIndex => $pageStickers) {
                    File::append($htmlPath, view('gso::reports.stickers.print.partials.page', [
                        'pageStickers' => $pageStickers,
                        'pageNo' => $pageIndex + 1,
                        'totalPages' => $totalPages,
                        'controls' => $payload['controls'],
                        'sheet' => $payload['sheet'],
                    ])->render());

                    $this->reportProgress($progress, [
                        'status' => 'running',
                        'stage' => sprintf('Prepared page %d of %d.', $pageIndex + 1, $totalPages),
                        'progress_percent' => $this->pageProgressPercent($pageIndex + 1, $totalPages),
                        'total_pages' => $totalPages,
                        'completed_pages' => $pageIndex + 1,
                    ]);
                }
            }

            File::append($htmlPath, $this->renderPdfDocumentEnd());

            $this->reportProgress($progress, [
                'status' => 'running',
                'stage' => 'Rendering the final PDF document...',
                'progress_percent' => self::FINALIZE_PROGRESS,
                'total_pages' => $totalPages,
                'completed_pages' => min($totalPages, max(1, $pages->count())),
            ]);

            $generatedPath = $this->pdfGenerator->generateFromHtmlFile($htmlPath, $finalOutputPath);

            $this->reportProgress($progress, [
                'status' => 'completed',
                'stage' => 'Sticker PDF is ready.',
                'progress_percent' => 100,
                'total_pages' => $totalPages,
                'completed_pages' => $totalPages,
            ]);

            return $generatedPath;
        } finally {
            File::deleteDirectory($buildDirectory);
        }
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, string>
     */
    private function normalizeInventoryItemIds(array $filters): array
    {
        $inventoryItemIds = collect($filters['inventory_item_ids'] ?? []);

        if (! is_array($filters['inventory_item_ids'] ?? null)) {
            $inventoryItemIds = collect([$filters['inventory_item_ids'] ?? null]);
        }

        $legacyInventoryItemId = $this->nullableTrim($filters['inventory_item_id'] ?? null);

        if ($legacyInventoryItemId !== null && ! $inventoryItemIds->contains($legacyInventoryItemId)) {
            $inventoryItemIds->prepend($legacyInventoryItemId);
        }

        return $inventoryItemIds
            ->flatten(1)
            ->map(fn ($value) => $this->nullableTrim($value))
            ->filter(static fn (?string $value): bool => $value !== null)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<int, string>  $inventoryItemIds
     * @return Collection<int, InventoryItem>
     */
    private function selectedInventoryItems(array $inventoryItemIds): Collection
    {
        if ($inventoryItemIds === []) {
            return collect();
        }

        $items = InventoryItem::query()
            ->withTrashed()
            ->with([
                'item' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'item_name', 'description', 'tracking_type']),
                'department' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'accountableOfficerRelation' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'full_name', 'office']),
            ])
            ->whereIn('id', $inventoryItemIds)
            ->get()
            ->keyBy(fn (InventoryItem $inventoryItem): string => (string) $inventoryItem->id);

        return collect($inventoryItemIds)
            ->map(fn (string $inventoryItemId): ?InventoryItem => $items->get($inventoryItemId))
            ->filter(static fn (?InventoryItem $inventoryItem): bool => $inventoryItem instanceof InventoryItem)
            ->values();
    }

    /**
     * @return array<int, array{id: string, title: string, label: string, description: string, classification: string}>
     */
    private function availableInventoryItems(?Collection $selectedInventoryItems = null): array
    {
        $items = InventoryItem::query()
            ->withTrashed()
            ->with([
                'item' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'item_name']),
            ])
            ->orderByDesc('acquisition_date')
            ->orderByDesc('created_at')
            ->get([
                'id',
                'item_id',
                'property_number',
                'stock_number',
                'description',
                'is_ics',
                'deleted_at',
            ]);

        $selectedInventoryItems
            ?->reverse()
            ->each(function (InventoryItem $selectedInventoryItem) use ($items): void {
                if (! $items->contains(fn (InventoryItem $item): bool => (string) $item->id === (string) $selectedInventoryItem->id)) {
                    $items->prepend($selectedInventoryItem);
                }
            });

        return $items
            ->map(fn (InventoryItem $inventoryItem): array => [
                'id' => (string) $inventoryItem->id,
                'title' => $this->nullableTrim($inventoryItem->property_number)
                    ?? $this->nullableTrim($inventoryItem->stock_number)
                    ?? Str::upper(Str::limit((string) $inventoryItem->id, 8, '')),
                'label' => $this->inventoryItemOptionLabel($inventoryItem),
                'description' => $this->inventoryItemOptionDescription($inventoryItem),
                'classification' => $inventoryItem->is_ics ? 'ICS' : 'PPE',
            ])
            ->unique('id')
            ->values()
            ->all();
    }

    private function buildSticker(InventoryItem $inventoryItem): array
    {
        $inventoryItem->loadMissing([
            'item',
            'department',
            'accountableOfficerRelation',
        ]);

        $reference = $this->resolveReference($inventoryItem);
        $description = $this->resolveDescription($inventoryItem);
        $publicAssetUrl = route('gso.public-assets.show', ['code' => $reference]);

        return [
            'inventory_item_id' => (string) $inventoryItem->id,
            'type_label' => $inventoryItem->is_ics ? 'ICS' : 'PPE',
            'reference' => $reference,
            'description' => Str::upper($description),
            'model_number' => Str::upper($this->nullableTrim($inventoryItem->model) ?? 'N/A'),
            'serial_number' => $this->nullableTrim($inventoryItem->serial_number) ?? 'N/A',
            'acquisition_date' => $inventoryItem->acquisition_date?->format('m-d-Y') ?? 'N/A',
            'acquisition_cost' => 'PHP ' . number_format((float) ($inventoryItem->acquisition_cost ?? 0), 2),
            'person_accountable' => Str::upper($this->resolveAccountableOfficer($inventoryItem)),
            'office_label' => $this->resolveOfficeLabel($inventoryItem),
            'barcode_text' => $this->nullableTrim($inventoryItem->serial_number) ?? $reference,
            'indicator_color' => $inventoryItem->is_ics ? '#f97316' : '#2563eb',
            'template_url' => asset('print/sticker.jpg'),
            'public_asset_url' => $publicAssetUrl,
            'inventory_item_url' => route('gso.inventory-items.show', ['inventoryItem' => $inventoryItem->id]),
            'qr_code_url' => 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . urlencode($publicAssetUrl),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function buildStickerCopies(array $baseSticker, int $copies): Collection
    {
        return collect(range(1, max(1, $copies)))
            ->map(fn (int $copyNumber): array => $baseSticker + [
                'copy_number' => $copyNumber,
            ]);
    }

    private function resolveReference(InventoryItem $inventoryItem): string
    {
        return $this->nullableTrim($inventoryItem->property_number)
            ?? $this->nullableTrim($inventoryItem->stock_number)
            ?? Str::upper((string) $inventoryItem->id);
    }

    private function resolveDescription(InventoryItem $inventoryItem): string
    {
        $description = $this->nullableTrim($inventoryItem->description)
            ?? $this->nullableTrim($inventoryItem->item?->description)
            ?? $this->nullableTrim($inventoryItem->item?->item_name)
            ?? 'INVENTORY ITEM';

        return Str::limit($description, 30, '...');
    }

    private function resolveAccountableOfficer(InventoryItem $inventoryItem): string
    {
        return $this->nullableTrim($inventoryItem->accountableOfficerRelation?->full_name)
            ?? $this->nullableTrim($inventoryItem->accountable_officer)
            ?? 'UNASSIGNED';
    }

    private function resolveOfficeLabel(InventoryItem $inventoryItem): string
    {
        $departmentLabel = $this->departmentLabel($inventoryItem->department);
        if ($departmentLabel !== null) {
            return Str::upper($departmentLabel);
        }

        return Str::upper(
            $this->nullableTrim($inventoryItem->accountableOfficerRelation?->office)
                ?? 'UNASSIGNED OFFICE'
        );
    }

    private function departmentLabel(?Department $department): ?string
    {
        if (! $department) {
            return null;
        }

        $code = $this->nullableTrim($department->code);
        $name = $this->nullableTrim($department->name);

        if ($code && $name) {
            return $code . ' - ' . $name;
        }

        return $code ?? $name;
    }

    private function inventoryItemOptionLabel(InventoryItem $inventoryItem): string
    {
        $reference = $this->nullableTrim($inventoryItem->property_number)
            ?? $this->nullableTrim($inventoryItem->stock_number)
            ?? Str::upper(Str::limit((string) $inventoryItem->id, 8, ''));

        $itemName = $this->nullableTrim($inventoryItem->item?->item_name)
            ?? $this->nullableTrim($inventoryItem->description)
            ?? 'Inventory Item';

        $label = trim($reference . ' - ' . $itemName);

        if ($inventoryItem->trashed()) {
            return $label . ' [Archived]';
        }

        return $label;
    }

    private function inventoryItemOptionDescription(InventoryItem $inventoryItem): string
    {
        $parts = array_filter([
            $this->nullableTrim($inventoryItem->item?->item_name)
                ?? $this->nullableTrim($inventoryItem->description)
                ?? 'Inventory Item',
            $this->nullableTrim($inventoryItem->stock_number),
            $inventoryItem->trashed() ? 'Archived' : null,
        ]);

        return implode(' | ', $parts);
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $clean = preg_replace('/\s+/', ' ', trim((string) $value)) ?? '';

        return $clean !== '' ? $clean : null;
    }

    private function localFileUrl(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);

        if (preg_match('/^([A-Za-z]):\/(.*)$/', $normalized, $matches) === 1) {
            $segments = array_map(
                static fn (string $segment): string => rawurlencode($segment),
                array_values(array_filter(
                    explode('/', $matches[2]),
                    static fn (string $segment): bool => $segment !== ''
                ))
            );

            return 'file:///' . $matches[1] . ':/' . implode('/', $segments);
        }

        return 'file:///' . ltrim($normalized, '/');
    }

    private function renderPdfDocumentStart(array $report, ?array $sticker, string $stickerBackgroundUrl): string
    {
        return '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>'
            . e($report['title'] ?? 'Sticker Printing')
            . '</title>'
            . view('gso::reports.stickers.print.partials.styles', [
                'sticker' => $sticker,
                'stickerBackgroundUrl' => $stickerBackgroundUrl,
            ])->render()
            . '</head><body>';
    }

    private function renderPdfDocumentEnd(): string
    {
        return '</body></html>';
    }

    private function pageProgressPercent(int $completedPages, int $totalPages): int
    {
        if ($totalPages <= 0) {
            return self::PAGE_PROGRESS_END;
        }

        $range = self::PAGE_PROGRESS_END - self::PAGE_PROGRESS_START;
        $ratio = min(max($completedPages / $totalPages, 0), 1);

        return (int) round(self::PAGE_PROGRESS_START + ($range * $ratio));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function reportProgress(?callable $progress, array $payload): void
    {
        if ($progress === null) {
            return;
        }

        $progress($payload);
    }
}
