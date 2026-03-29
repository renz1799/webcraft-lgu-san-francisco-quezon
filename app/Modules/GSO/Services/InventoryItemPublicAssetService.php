<?php

namespace App\Modules\GSO\Services;

use App\Core\Models\Department;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\InventoryItemFile;
use App\Modules\GSO\Services\Contracts\InventoryItemFileServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemPublicAssetServiceInterface;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryFileTypes;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InventoryItemPublicAssetService implements InventoryItemPublicAssetServiceInterface
{
    public function __construct(
        private readonly InventoryItemFileServiceInterface $files,
    ) {}

    public function getPublicAssetPagePayload(string $code): array
    {
        $inventoryItem = $this->resolveInventoryItem($code, true);
        $publicCode = $this->resolvePublicCode($inventoryItem);
        $photos = $inventoryItem->files
            ->values()
            ->map(fn (InventoryItemFile $file): array => [
                'id' => (string) $file->id,
                'url' => route('gso.public-assets.files.preview', [
                    'code' => $publicCode,
                    'file' => $file->id,
                ]),
                'caption' => $this->nullableString($file->caption)
                    ?? $this->nullableString($file->original_name)
                    ?? 'Asset photo',
                'is_primary' => (bool) $file->is_primary,
            ])
            ->all();

        return [
            'view' => 'gso::inventory-items.public-show',
            'data' => [
                'asset' => [
                    'type_label' => (bool) $inventoryItem->is_ics ? 'ICS' : 'PPE',
                    'reference_label' => $this->referenceLabel($inventoryItem),
                    'reference_value' => $publicCode,
                    'item_name' => $this->nullableString($inventoryItem->item?->item_name)
                        ?? $this->nullableString($inventoryItem->description)
                        ?? 'Inventory Item',
                    'description' => $this->nullableString($inventoryItem->description)
                        ?? $this->nullableString($inventoryItem->item?->item_name)
                        ?? 'Inventory Item',
                    'brand' => $this->nullableString($inventoryItem->brand) ?? 'N/A',
                    'model' => $this->nullableString($inventoryItem->model) ?? 'N/A',
                    'serial_number' => $this->nullableString($inventoryItem->serial_number) ?? 'N/A',
                    'acquisition_date' => $inventoryItem->acquisition_date?->format('F d, Y') ?? 'N/A',
                    'acquisition_cost' => is_numeric($inventoryItem->acquisition_cost)
                        ? 'P' . number_format((float) $inventoryItem->acquisition_cost, 2)
                        : 'N/A',
                    'office' => $this->departmentLabel($inventoryItem->department),
                    'status' => InventoryStatuses::labels()[(string) ($inventoryItem->status ?? '')]
                        ?? $this->humanize((string) ($inventoryItem->status ?? 'unknown')),
                    'condition' => InventoryConditions::labels()[(string) ($inventoryItem->condition ?? '')]
                        ?? $this->humanize((string) ($inventoryItem->condition ?? 'unknown')),
                    'photos' => $photos,
                    'primary_photo_url' => $photos[0]['url'] ?? null,
                ],
            ],
        ];
    }

    public function streamPublicAssetFile(string $code, string $fileId): array
    {
        $inventoryItem = $this->resolveInventoryItem($code, false);
        $file = InventoryItemFile::query()
            ->where('inventory_item_id', (string) $inventoryItem->id)
            ->where('type', InventoryFileTypes::PHOTO)
            ->whereKey($fileId)
            ->first();

        if (! $file) {
            throw (new ModelNotFoundException())->setModel(InventoryItemFile::class, [$fileId]);
        }

        return $this->files->preview((string) $inventoryItem->id, $fileId);
    }

    private function resolveInventoryItem(string $code, bool $withFiles): InventoryItem
    {
        $normalized = trim(urldecode($code));
        $lower = mb_strtolower($normalized, 'UTF-8');

        $query = InventoryItem::query()
            ->with([
                'item' => fn ($itemQuery) => $itemQuery
                    ->withTrashed()
                    ->select(['id', 'item_name', 'item_identification']),
                'department' => fn ($departmentQuery) => $departmentQuery
                    ->withTrashed()
                    ->select(['id', 'code', 'name']),
            ])
            ->where(function (Builder $builder) use ($lower): void {
                $builder->whereRaw('LOWER(COALESCE(property_number, \'\')) = ?', [$lower])
                    ->orWhereRaw('LOWER(COALESCE(stock_number, \'\')) = ?', [$lower])
                    ->orWhere('id', $lower);
            });

        if ($withFiles) {
            $query->with([
                'files' => fn ($fileQuery) => $fileQuery
                    ->where('type', InventoryFileTypes::PHOTO)
                    ->orderByDesc('is_primary')
                    ->orderBy('position')
                    ->orderBy('created_at'),
            ]);
        }

        $inventoryItem = $query->first();

        if (! $inventoryItem) {
            throw (new ModelNotFoundException())->setModel(InventoryItem::class, [$code]);
        }

        return $inventoryItem;
    }

    private function resolvePublicCode(InventoryItem $inventoryItem): string
    {
        return (string) (
            $this->nullableString($inventoryItem->property_number)
            ?? $this->nullableString($inventoryItem->stock_number)
            ?? $inventoryItem->id
        );
    }

    private function referenceLabel(InventoryItem $inventoryItem): string
    {
        if ($this->nullableString($inventoryItem->property_number) !== null) {
            return 'Property Number';
        }

        if ($this->nullableString($inventoryItem->stock_number) !== null) {
            return 'Stock Number';
        }

        return 'Record Code';
    }

    private function departmentLabel(?Department $department): string
    {
        if (! $department) {
            return 'Unassigned Office';
        }

        $code = trim((string) ($department->code ?? ''));
        $name = trim((string) ($department->name ?? ''));

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Unassigned Office');
    }

    private function humanize(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return 'Unknown';
        }

        return ucwords(str_replace('_', ' ', $value));
    }

    private function nullableString(mixed $value): ?string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) ($value ?? ''))) ?? '';

        return $value !== '' ? $value : null;
    }
}
