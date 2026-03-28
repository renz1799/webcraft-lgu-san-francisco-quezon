<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemCardPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\PropertyCardsReportServiceInterface;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PropertyCardsReportService implements PropertyCardsReportServiceInterface
{
    private const FILTER_KEYS = [
        'search',
        'inventory_item_id',
        'department_id',
        'item_id',
        'fund_source_id',
        'classification',
        'custody_state',
        'inventory_status',
        'archived',
    ];

    public function __construct(
        private readonly InventoryItemRepositoryInterface $inventoryItems,
        private readonly InventoryItemCardPrintServiceInterface $cardPrinter,
        private readonly PdfGeneratorInterface $pdfGenerator,
        private readonly PrintConfigLoaderInterface $printConfigLoader,
    ) {}

    public function getPrintViewData(array $filters = [], ?string $requestedPaper = null): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        $size = max(1, min((int) ($filters['size'] ?? 12), 50));
        $tableFilters = $this->tableFilters($filters);
        $paginator = $this->inventoryItems->paginateForTable($tableFilters, $page, $size);
        $cards = $this->buildCards($paginator->getCollection());
        $paperProfile = $this->printConfigLoader->resolvePaperProfile('gso_property_cards', $requestedPaper);
        $summary = [
            'total_matching' => (int) $paginator->total(),
            'cards_in_batch' => count($cards),
            'source_page' => $page,
            'source_page_size' => $size,
            'ppe_cards' => count(array_filter($cards, fn (array $card): bool => ($card['type'] ?? '') === 'pc')),
            'ics_cards' => count(array_filter($cards, fn (array $card): bool => ($card['type'] ?? '') === 'ics')),
        ];

        return [
            'report' => [
                'title' => 'Property Cards',
                'document' => [
                    'filters' => [
                        'search' => $this->nullableTrim($filters['search'] ?? null),
                        'inventory_item' => $this->selectedInventoryItemLabel($filters['inventory_item_id'] ?? null),
                        'department' => $this->selectedDepartmentLabel($filters['department_id'] ?? null),
                        'item' => $this->selectedItemLabel($filters['item_id'] ?? null),
                        'fund_source' => $this->selectedFundLabel($filters['fund_source_id'] ?? null),
                        'classification' => $this->classificationLabel($filters['classification'] ?? null),
                        'custody_state' => $this->custodyLabel($filters['custody_state'] ?? null),
                        'inventory_status' => $this->inventoryStatusLabel($filters['inventory_status'] ?? null),
                        'record_status' => $this->recordStatusLabel($filters['archived'] ?? null),
                    ],
                    'summary' => $summary,
                ],
                'cards' => $cards,
            ],
            'paperProfile' => $paperProfile,
            'available_funds' => $this->availableFunds(),
            'available_departments' => $this->availableDepartments(),
            'available_items' => $this->availableItems(),
            'classification_options' => [
                'ppe' => 'PPE',
                'ics' => 'ICS',
            ],
            'custody_options' => InventoryCustodyStates::labels(),
            'inventory_status_options' => InventoryStatuses::labels(),
            'record_status_options' => [
                'active' => 'Active',
                'archived' => 'Archived',
                'all' => 'All',
            ],
        ];
    }

    public function generatePdf(array $filters = [], ?string $requestedPaper = null): string
    {
        $payload = $this->getPrintViewData($filters, $requestedPaper);
        $filename = 'property-cards-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.pdf';
        $outputPath = storage_path('app/tmp/' . $filename);

        return $this->pdfGenerator->generateFromView(
            view: 'gso::reports.property-cards.print.pdf',
            data: [
                'report' => $payload['report'],
                'paperProfile' => $payload['paperProfile'],
            ],
            outputPath: $outputPath,
        );
    }

    /**
     * @param  Collection<int, InventoryItem>  $inventoryItems
     * @return array<int, array<string, mixed>>
     */
    private function buildCards(Collection $inventoryItems): array
    {
        return $inventoryItems
            ->map(function (InventoryItem $inventoryItem): array {
                $payload = $this->cardPrinter->getPropertyCardPrintPayload($inventoryItem, [
                    'preview' => true,
                ]);

                return [
                    'type' => str_contains((string) ($payload['view'] ?? ''), 'ics-print') ? 'ics' : 'pc',
                    'inventory_item_id' => (string) $inventoryItem->id,
                    'card' => $payload['data']['card'] ?? [],
                    'entries' => $payload['data']['entries'] ?? [],
                    'maxGridRows' => (int) ($payload['data']['maxGridRows'] ?? 18),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function tableFilters(array $filters): array
    {
        $tableFilters = [];

        foreach (self::FILTER_KEYS as $key) {
            if (! array_key_exists($key, $filters)) {
                continue;
            }

            $value = $filters[$key];

            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === '' || $value === null) {
                continue;
            }

            $tableFilters[$key] = $value;
        }

        return $tableFilters;
    }

    /**
     * @return array<int, array{id: string, label: string}>
     */
    private function availableFunds(): array
    {
        return FundSource::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(fn (FundSource $fundSource): array => [
                'id' => (string) $fundSource->id,
                'label' => trim(($fundSource->code ? $fundSource->code . ' - ' : '') . $fundSource->name),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, label: string}>
     */
    private function availableDepartments(): array
    {
        return Department::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'code', 'name'])
            ->map(fn (Department $department): array => [
                'id' => (string) $department->id,
                'label' => trim($department->code . ' - ' . $department->name, ' -'),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, label: string}>
     */
    private function availableItems(): array
    {
        return Item::query()
            ->whereNull('deleted_at')
            ->where('tracking_type', 'property')
            ->orderBy('item_name')
            ->get(['id', 'item_name', 'item_identification'])
            ->map(fn (Item $item): array => [
                'id' => (string) $item->id,
                'label' => trim($item->item_name . ($item->item_identification ? ' (' . $item->item_identification . ')' : '')),
            ])
            ->values()
            ->all();
    }

    private function selectedDepartmentLabel(?string $departmentId): string
    {
        $departmentId = $this->nullableTrim($departmentId);
        if ($departmentId === null) {
            return 'All Departments';
        }

        $department = Department::query()
            ->whereNull('deleted_at')
            ->find($departmentId, ['code', 'name']);

        if (! $department) {
            return 'Selected Department';
        }

        return trim(($department->code ? $department->code . ' - ' : '') . $department->name);
    }

    private function selectedInventoryItemLabel(?string $inventoryItemId): string
    {
        $inventoryItemId = $this->nullableTrim($inventoryItemId);
        if ($inventoryItemId === null) {
            return 'All Inventory Items';
        }

        $inventoryItem = InventoryItem::query()
            ->withTrashed()
            ->with([
                'item' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'item_name']),
            ])
            ->find($inventoryItemId, [
                'id',
                'item_id',
                'property_number',
                'stock_number',
                'description',
            ]);

        if (! $inventoryItem) {
            return 'Selected Inventory Item';
        }

        $primaryLabel = $inventoryItem->property_number
            ?: $inventoryItem->stock_number
            ?: $inventoryItem->item?->item_name
            ?: $inventoryItem->description
            ?: 'Selected Inventory Item';

        $secondaryLabel = $inventoryItem->item?->item_name;

        if ($secondaryLabel && $secondaryLabel !== $primaryLabel) {
            return trim($primaryLabel . ' - ' . $secondaryLabel);
        }

        return $primaryLabel;
    }

    private function selectedItemLabel(?string $itemId): string
    {
        $itemId = $this->nullableTrim($itemId);
        if ($itemId === null) {
            return 'All Items';
        }

        $item = Item::query()
            ->whereNull('deleted_at')
            ->find($itemId, ['item_name', 'item_identification']);

        if (! $item) {
            return 'Selected Item';
        }

        return trim($item->item_name . ($item->item_identification ? ' (' . $item->item_identification . ')' : ''));
    }

    private function selectedFundLabel(?string $fundSourceId): string
    {
        $fundSourceId = $this->nullableTrim($fundSourceId);
        if ($fundSourceId === null) {
            return 'All Fund Sources';
        }

        $fundSource = FundSource::query()
            ->whereNull('deleted_at')
            ->find($fundSourceId, ['code', 'name']);

        if (! $fundSource) {
            return 'Selected Fund Source';
        }

        return trim(($fundSource->code ? $fundSource->code . ' - ' : '') . $fundSource->name);
    }

    private function classificationLabel(?string $value): string
    {
        return match ($this->nullableTrim($value)) {
            'ppe' => 'PPE',
            'ics' => 'ICS',
            default => 'All Classes',
        };
    }

    private function custodyLabel(?string $value): string
    {
        $value = $this->nullableTrim($value);

        return $value !== null
            ? (InventoryCustodyStates::labels()[$value] ?? 'Selected Custody')
            : 'All Custody';
    }

    private function inventoryStatusLabel(?string $value): string
    {
        $value = $this->nullableTrim($value);

        return $value !== null
            ? (InventoryStatuses::labels()[$value] ?? 'Selected Status')
            : 'All Statuses';
    }

    private function recordStatusLabel(?string $value): string
    {
        return match ($this->nullableTrim($value) ?? 'active') {
            'archived' => 'Archived',
            'all' => 'All Records',
            default => 'Active Records',
        };
    }

    private function nullableTrim(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $clean = trim($value);

        return $clean !== '' ? $clean : null;
    }
}
