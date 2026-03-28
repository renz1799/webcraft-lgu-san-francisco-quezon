<?php

namespace App\Modules\GSO\Http\Controllers\InventoryItems;

use App\Core\Models\Department;
use App\Http\Controllers\Controller;
use App\Modules\GSO\Http\Requests\InventoryItems\InventoryItemTableDataRequest;
use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\InventoryItemFile;
use App\Modules\GSO\Models\Inspection;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Services\Contracts\InventoryItemServiceInterface;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryFileTypes;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryItemController extends Controller
{
    public function __construct(
        private readonly InventoryItemServiceInterface $inventoryItems,
    ) {
        $this->middleware('role_or_permission:Administrator|admin|view Inventory Items|modify Inventory Items')
            ->only(['index', 'data', 'show']);
    }

    public function index(): View
    {
        return view('gso::inventory-items.index', [
            'items' => Item::query()
                ->with(['asset' => fn ($query) => $query->select(['id', 'asset_code', 'asset_name'])])
                ->whereNull('deleted_at')
                ->where('tracking_type', 'property')
                ->orderBy('item_name')
                ->get(['id', 'asset_id', 'item_name', 'item_identification', 'base_unit', 'requires_serial']),
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'fundSources' => FundSource::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'accountableOfficers' => AccountableOfficer::query()
                ->whereNull('deleted_at')
                ->orderBy('full_name')
                ->get(['id', 'full_name']),
            'inspections' => Inspection::query()
                ->whereNull('deleted_at')
                ->orderByDesc('created_at')
                ->get(['id', 'item_name', 'po_number', 'dv_number', 'status']),
            'inventoryStatuses' => InventoryStatuses::labels(),
            'inventoryConditions' => InventoryConditions::labels(),
            'inventoryCustodyStates' => InventoryCustodyStates::labels(),
            'inventoryEventTypes' => InventoryEventTypes::manualLabels(),
            'inventoryFileTypes' => InventoryFileTypes::labels(),
        ]);
    }

    public function data(InventoryItemTableDataRequest $request): JsonResponse
    {
        $payload = $this->inventoryItems->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function show(string $inventoryItem): View
    {
        $inventoryItem = InventoryItem::query()
            ->withTrashed()
            ->with([
                'item' => fn ($query) => $query
                    ->withTrashed()
                    ->with([
                        'asset' => fn ($assetQuery) => $assetQuery
                            ->withTrashed()
                            ->select(['id', 'asset_code', 'asset_name']),
                    ])
                    ->select([
                        'id',
                        'asset_id',
                        'item_name',
                        'item_identification',
                        'description',
                        'tracking_type',
                        'major_sub_account_group',
                    ]),
                'department' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'fundSource' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
                'accountableOfficerRelation' => fn ($query) => $query
                    ->withTrashed()
                    ->select(['id', 'full_name', 'designation', 'office']),
                'files' => fn ($query) => $query
                    ->where('type', InventoryFileTypes::PHOTO)
                    ->orderByDesc('is_primary')
                    ->orderBy('position')
                    ->orderBy('created_at'),
                'events' => fn ($query) => $query
                    ->with([
                        'department' => fn ($departmentQuery) => $departmentQuery
                            ->withTrashed()
                            ->select(['id', 'code', 'name']),
                    ])
                    ->orderByDesc('event_date')
                    ->orderByDesc('created_at'),
            ])
            ->findOrFail($inventoryItem);

        $photoFiles = $inventoryItem->files
            ->map(fn (InventoryItemFile $file): array => [
                'id' => (string) $file->id,
                'url' => route('gso.inventory-items.files.preview', [
                    'inventoryItem' => $inventoryItem->id,
                    'file' => $file->id,
                ]),
                'caption' => $this->nullableString($file->caption)
                    ?? $this->nullableString($file->original_name)
                    ?? 'Inventory photo',
                'is_primary' => (bool) $file->is_primary,
            ])
            ->values()
            ->all();

        return view('gso::inventory-items.show', [
            'inventoryItem' => $inventoryItem,
            'photoFiles' => $photoFiles,
            'linkedRecords' => $this->buildLinkedRecords($inventoryItem),
            'departments' => Department::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'fundSources' => FundSource::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(['id', 'code', 'name']),
            'publicAssetUrl' => route('gso.public-assets.show', [
                'code' => $this->publicAssetCode($inventoryItem),
            ]),
            'propertyCardUrl' => route('gso.reports.property-cards.print', $this->propertyCardReportParams($inventoryItem)),
        ]);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function buildLinkedRecords(InventoryItem $inventoryItem): array
    {
        $records = [];

        foreach ($inventoryItem->events as $event) {
            $referenceType = $this->nullableString($event->reference_type);
            $referenceId = $this->nullableString($event->reference_id);
            $referenceNo = $this->nullableString($event->reference_no);

            if ($referenceType === null && $referenceNo === null) {
                continue;
            }

            $key = implode('|', [
                Str::lower($referenceType ?? 'reference'),
                $referenceId ?? '',
                $referenceNo ?? '',
            ]);

            if (array_key_exists($key, $records)) {
                continue;
            }

            $records[$key] = [
                'label' => $referenceNo
                    ?? Str::headline((string) ($referenceType ?? 'Reference')),
                'meta' => collect([
                    $referenceType ? Str::headline($referenceType) : null,
                    $event->event_date?->format('M d, Y'),
                ])->filter()->implode(' | '),
                'url' => $referenceId ? $this->referenceUrlFor($referenceType, $referenceId) : null,
            ];
        }

        return array_values($records);
    }

    private function referenceUrlFor(?string $referenceType, string $referenceId): ?string
    {
        $type = Str::lower(trim((string) $referenceType));

        return match ($type) {
            'air' => route('gso.air.inspect', ['air' => $referenceId]),
            'inspection' => route('gso.inspections.show', ['inspection' => $referenceId]),
            'task', 'workflow task' => route('gso.tasks.show', ['id' => $referenceId]),
            'par' => route('gso.pars.show', ['par' => $referenceId]),
            'ics' => route('gso.ics.edit', ['ics' => $referenceId]),
            'itr' => route('gso.itrs.edit', ['itr' => $referenceId]),
            'ptr' => route('gso.ptrs.edit', ['ptr' => $referenceId]),
            'wmr' => route('gso.wmrs.edit', ['wmr' => $referenceId]),
            'ris' => route('gso.ris.edit', ['ris' => $referenceId]),
            default => null,
        };
    }

    private function publicAssetCode(InventoryItem $inventoryItem): string
    {
        return (string) (
            $this->nullableString($inventoryItem->property_number)
            ?? $this->nullableString($inventoryItem->stock_number)
            ?? $inventoryItem->id
        );
    }

    /**
     * @return array<string, int|string>
     */
    private function propertyCardReportParams(InventoryItem $inventoryItem): array
    {
        return array_filter([
            'preview' => 1,
            'inventory_item_id' => (string) $inventoryItem->id,
            'department_id' => $inventoryItem->department_id ? (string) $inventoryItem->department_id : null,
            'item_id' => $inventoryItem->item_id ? (string) $inventoryItem->item_id : null,
            'fund_source_id' => $inventoryItem->fund_source_id ? (string) $inventoryItem->fund_source_id : null,
            'classification' => $inventoryItem->is_ics ? 'ics' : 'ppe',
            'custody_state' => $this->nullableString($inventoryItem->custody_state),
            'inventory_status' => $this->nullableString($inventoryItem->status),
            'page' => 1,
            'size' => 1,
        ], static fn ($value) => $value !== null && $value !== '');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
