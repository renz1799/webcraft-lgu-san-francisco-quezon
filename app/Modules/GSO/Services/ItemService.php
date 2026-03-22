<?php

namespace App\Modules\GSO\Services;

use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Modules\GSO\Builders\Contracts\ItemDatatableRowBuilderInterface;
use App\Modules\GSO\Models\AssetCategory;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Repositories\Contracts\ItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\ItemUnitConversionRepositoryInterface;
use App\Modules\GSO\Services\Contracts\ItemServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ItemService implements ItemServiceInterface
{
    public function __construct(
        private readonly ItemRepositoryInterface $items,
        private readonly ItemUnitConversionRepositoryInterface $unitConversions,
        private readonly AuditLogServiceInterface $auditLogs,
        private readonly ItemDatatableRowBuilderInterface $datatableRowBuilder,
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int) ($params['page'] ?? 1));
        $size = max(1, min((int) ($params['size'] ?? 15), 100));
        $paginator = $this->items->paginateForTable($params, $page, $size);

        return [
            'data' => $paginator->getCollection()
                ->map(fn (Item $item) => $this->datatableRowBuilder->build($item))
                ->values()
                ->all(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ];
    }

    public function getForEdit(string $itemId): array
    {
        $item = $this->items->findOrFail($itemId);

        return $this->datatableRowBuilder->build($item);
    }

    public function create(string $actorUserId, array $data): Item
    {
        return DB::transaction(function () use ($actorUserId, $data) {
            $payload = $this->normalizedPayload($data);
            $this->assertTrackingRules($payload['tracking_type'], (bool) $payload['requires_serial']);

            $item = $this->items->create($payload);
            $unitConversions = $this->syncUnitConversions((string) $item->id, $data['unit_conversions'] ?? []);

            $this->auditLogs->record(
                action: 'gso.item.created',
                subject: $item,
                changesOld: [],
                changesNew: array_merge($item->only([
                    'asset_id',
                    'item_name',
                    'description',
                    'base_unit',
                    'item_identification',
                    'major_sub_account_group',
                    'tracking_type',
                    'requires_serial',
                    'is_semi_expendable',
                    'is_selected',
                ]), [
                    'unit_conversions' => $unitConversions,
                ]),
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO item created: ' . $this->itemLabel($item),
                display: $this->buildCreatedDisplay($item, $unitConversions),
            );

            return $this->items->findOrFail((string) $item->id);
        });
    }

    public function update(string $actorUserId, string $itemId, array $data): Item
    {
        return DB::transaction(function () use ($actorUserId, $itemId, $data) {
            $item = $this->items->findOrFail($itemId);
            $before = array_merge($item->only([
                'asset_id',
                'item_name',
                'description',
                'base_unit',
                'item_identification',
                'major_sub_account_group',
                'tracking_type',
                'requires_serial',
                'is_semi_expendable',
                'is_selected',
            ]), [
                'unit_conversions' => $this->formatUnitConversionRows(
                    $this->unitConversions->getByItemId((string) $item->id)
                ),
            ]);

            $payload = $this->normalizedPayload($data, $item);
            $this->assertTrackingRules($payload['tracking_type'], (bool) $payload['requires_serial']);

            $item->fill($payload);
            $item = $this->items->save($item);
            $unitConversions = array_key_exists('unit_conversions', $data)
                ? $this->syncUnitConversions((string) $item->id, $data['unit_conversions'] ?? [])
                : $before['unit_conversions'];

            $after = array_merge($item->only([
                'asset_id',
                'item_name',
                'description',
                'base_unit',
                'item_identification',
                'major_sub_account_group',
                'tracking_type',
                'requires_serial',
                'is_semi_expendable',
                'is_selected',
            ]), [
                'unit_conversions' => $unitConversions,
            ]);

            $this->auditLogs->record(
                action: 'gso.item.updated',
                subject: $item,
                changesOld: $before,
                changesNew: $after,
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO item updated: ' . $this->itemLabel($item),
                display: $this->buildUpdatedDisplay($before, $after),
            );

            return $this->items->findOrFail((string) $item->id);
        });
    }

    public function delete(string $actorUserId, string $itemId): void
    {
        DB::transaction(function () use ($actorUserId, $itemId) {
            $item = $this->items->findOrFail($itemId);
            $before = $item->only([
                'asset_id',
                'item_name',
                'tracking_type',
                'requires_serial',
                'is_semi_expendable',
            ]);

            $this->items->delete($item);

            $this->auditLogs->record(
                action: 'gso.item.deleted',
                subject: $item,
                changesOld: $before,
                changesNew: ['deleted_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO item archived: ' . $this->itemLabel($item),
                display: $this->buildLifecycleDisplay($item, 'Active Record', 'Archived'),
            );
        });
    }

    public function restore(string $actorUserId, string $itemId): void
    {
        DB::transaction(function () use ($actorUserId, $itemId) {
            $item = $this->items->findOrFail($itemId, true);

            if (! $item->trashed()) {
                return;
            }

            $deletedAt = $item->deleted_at?->toDateTimeString();
            $this->items->restore($item);

            $this->auditLogs->record(
                action: 'gso.item.restored',
                subject: $item,
                changesOld: ['deleted_at' => $deletedAt],
                changesNew: ['restored_at' => now()->toDateTimeString()],
                meta: ['actor_user_id' => $actorUserId],
                message: 'GSO item restored: ' . $this->itemLabel($item),
                display: $this->buildLifecycleDisplay($item, 'Archived', 'Active Record'),
            );
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizedPayload(array $data, ?Item $item = null): array
    {
        return [
            'asset_id' => trim((string) ($data['asset_id'] ?? $item?->asset_id ?? '')),
            'item_name' => $this->cleanValue((string) ($data['item_name'] ?? $item?->item_name ?? '')),
            'description' => $this->nullableString($data['description'] ?? $item?->description),
            'base_unit' => $this->nullableString($data['base_unit'] ?? $item?->base_unit),
            'item_identification' => $this->nullableString($data['item_identification'] ?? $item?->item_identification),
            'major_sub_account_group' => $this->nullableString($data['major_sub_account_group'] ?? $item?->major_sub_account_group),
            'tracking_type' => trim((string) ($data['tracking_type'] ?? $item?->tracking_type ?? 'property')),
            'requires_serial' => array_key_exists('requires_serial', $data)
                ? (bool) $data['requires_serial']
                : (bool) ($item?->requires_serial ?? false),
            'is_semi_expendable' => array_key_exists('is_semi_expendable', $data)
                ? (bool) $data['is_semi_expendable']
                : (bool) ($item?->is_semi_expendable ?? false),
            'is_selected' => array_key_exists('is_selected', $data)
                ? (bool) $data['is_selected']
                : (bool) ($item?->is_selected ?? false),
        ];
    }

    private function assertTrackingRules(string $trackingType, bool $requiresSerial): void
    {
        if (! in_array($trackingType, ['property', 'consumable'], true)) {
            throw ValidationException::withMessages([
                'tracking_type' => ['Tracking type must be either property or consumable.'],
            ]);
        }

        if ($trackingType === 'consumable' && $requiresSerial) {
            throw ValidationException::withMessages([
                'requires_serial' => ['Consumable items cannot require serial numbers.'],
            ]);
        }
    }

    /**
     * @param  mixed  $input
     * @return array<int, array{from_unit: string, multiplier: int}>
     */
    private function syncUnitConversions(string $itemId, mixed $input): array
    {
        $normalized = $this->normalizeUnitConversions($input);
        $keepFromUnits = array_keys($normalized);

        $this->unitConversions->softDeleteMissing($itemId, $keepFromUnits);

        foreach ($normalized as $fromUnit => $multiplier) {
            $this->unitConversions->upsertOne($itemId, $fromUnit, $multiplier);
        }

        return $this->formatUnitConversionRows(
            $this->unitConversions->getByItemId($itemId)
        );
    }

    /**
     * @param  mixed  $input
     * @return array<string, int>
     */
    private function normalizeUnitConversions(mixed $input): array
    {
        if (! is_array($input)) {
            return [];
        }

        $normalized = [];

        foreach ($input as $row) {
            if (! is_array($row)) {
                continue;
            }

            $fromUnit = strtolower($this->cleanValue((string) ($row['from_unit'] ?? '')));
            $multiplier = (int) ($row['multiplier'] ?? 0);

            if ($fromUnit === '' && $multiplier === 0) {
                continue;
            }

            if ($fromUnit === '' || $multiplier < 1) {
                continue;
            }

            $normalized[$fromUnit] = $multiplier;
        }

        return $normalized;
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array<int, array{from_unit: string, multiplier: int}>
     */
    private function formatUnitConversionRows(array $rows): array
    {
        return array_values(array_map(
            fn ($row): array => [
                'from_unit' => (string) $row->from_unit,
                'multiplier' => (int) $row->multiplier,
            ],
            $rows,
        ));
    }

    /**
     * @param  array<int, array{from_unit: string, multiplier: int}>  $rows
     */
    private function formatUnitConversionSummary(array $rows, ?string $baseUnit): string
    {
        if ($rows === []) {
            return 'None';
        }

        $baseUnit = trim((string) ($baseUnit ?? ''));

        return implode(', ', array_map(
            fn (array $row): string => $baseUnit !== ''
                ? "{$row['from_unit']} = {$row['multiplier']} {$baseUnit}"
                : "{$row['from_unit']} = {$row['multiplier']}",
            $rows,
        ));
    }

    private function buildCreatedDisplay(Item $item, array $unitConversions): array
    {
        return [
            'summary' => 'Item created: ' . $this->itemLabel($item),
            'subject_label' => $this->itemLabel($item),
            'sections' => [[
                'title' => 'Item Details',
                'items' => [
                    ['label' => 'Asset Category', 'before' => 'None', 'after' => $this->resolveAssetCategoryLabel($item->asset_id)],
                    ['label' => 'Item Name', 'before' => 'None', 'after' => $item->item_name],
                    ['label' => 'Identification', 'before' => 'None', 'after' => $this->displayValue($item->item_identification)],
                    ['label' => 'Base Unit', 'before' => 'None', 'after' => $this->displayValue($item->base_unit)],
                    ['label' => 'Tracking Type', 'before' => 'None', 'after' => $this->trackingTypeLabel($item->tracking_type)],
                    ['label' => 'Requires Serial', 'before' => 'None', 'after' => $this->booleanLabel($item->requires_serial)],
                    ['label' => 'Semi-Expendable', 'before' => 'None', 'after' => $this->booleanLabel($item->is_semi_expendable)],
                    ['label' => 'Unit Conversions', 'before' => 'None', 'after' => $this->formatUnitConversionSummary($unitConversions, $item->base_unit)],
                ],
            ]],
        ];
    }

    private function buildUpdatedDisplay(array $before, array $after): array
    {
        return [
            'summary' => 'Item updated: ' . $this->itemLabelFromValues($after),
            'subject_label' => $this->itemLabelFromValues($after),
            'sections' => [[
                'title' => 'Item Details',
                'items' => [
                    ['label' => 'Asset Category', 'before' => $this->resolveAssetCategoryLabel($before['asset_id'] ?? null), 'after' => $this->resolveAssetCategoryLabel($after['asset_id'] ?? null)],
                    ['label' => 'Item Name', 'before' => $this->displayValue($before['item_name'] ?? null), 'after' => $this->displayValue($after['item_name'] ?? null)],
                    ['label' => 'Identification', 'before' => $this->displayValue($before['item_identification'] ?? null), 'after' => $this->displayValue($after['item_identification'] ?? null)],
                    ['label' => 'Base Unit', 'before' => $this->displayValue($before['base_unit'] ?? null), 'after' => $this->displayValue($after['base_unit'] ?? null)],
                    ['label' => 'Tracking Type', 'before' => $this->trackingTypeLabel((string) ($before['tracking_type'] ?? '')), 'after' => $this->trackingTypeLabel((string) ($after['tracking_type'] ?? ''))],
                    ['label' => 'Requires Serial', 'before' => $this->booleanLabel($before['requires_serial'] ?? null), 'after' => $this->booleanLabel($after['requires_serial'] ?? null)],
                    ['label' => 'Semi-Expendable', 'before' => $this->booleanLabel($before['is_semi_expendable'] ?? null), 'after' => $this->booleanLabel($after['is_semi_expendable'] ?? null)],
                    [
                        'label' => 'Unit Conversions',
                        'before' => $this->formatUnitConversionSummary((array) ($before['unit_conversions'] ?? []), $before['base_unit'] ?? null),
                        'after' => $this->formatUnitConversionSummary((array) ($after['unit_conversions'] ?? []), $after['base_unit'] ?? null),
                    ],
                ],
            ]],
        ];
    }

    private function buildLifecycleDisplay(Item $item, string $before, string $after): array
    {
        return [
            'summary' => 'Item ' . strtolower($after === 'Archived' ? 'archived' : 'restored') . ': ' . $this->itemLabel($item),
            'subject_label' => $this->itemLabel($item),
            'sections' => [[
                'title' => 'Item Lifecycle',
                'items' => [
                    ['label' => 'Archive Status', 'before' => $before, 'after' => $after],
                ],
            ]],
        ];
    }

    private function itemLabel(Item $item): string
    {
        return $this->itemLabelFromValues($item->toArray());
    }

    /**
     * @param  array<string, mixed>  $values
     */
    private function itemLabelFromValues(array $values): string
    {
        $itemName = $this->cleanValue((string) ($values['item_name'] ?? ''));
        $identification = $this->cleanValue((string) ($values['item_identification'] ?? ''));

        if ($itemName !== '' && $identification !== '') {
            return "{$itemName} ({$identification})";
        }

        return $itemName !== '' ? $itemName : ($identification !== '' ? $identification : 'Item');
    }

    private function resolveAssetCategoryLabel(?string $assetId): string
    {
        $assetId = trim((string) ($assetId ?? ''));

        if ($assetId === '') {
            return 'None';
        }

        $assetCategory = AssetCategory::query()
            ->withTrashed()
            ->select(['id', 'asset_code', 'asset_name'])
            ->find($assetId);

        if (! $assetCategory) {
            return 'Unknown Asset Category';
        }

        $code = trim((string) $assetCategory->asset_code);
        $name = trim((string) $assetCategory->asset_name);

        if ($code !== '' && $name !== '') {
            return "{$code} ({$name})";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Asset Category');
    }

    private function trackingTypeLabel(?string $value): string
    {
        return match (trim((string) ($value ?? ''))) {
            'property' => 'Property',
            'consumable' => 'Consumable',
            default => 'None',
        };
    }

    private function booleanLabel(mixed $value): string
    {
        if ($value === null) {
            return 'None';
        }

        return (bool) $value ? 'Yes' : 'No';
    }

    private function displayValue(mixed $value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : 'None';
    }

    private function cleanValue(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim($value)) ?? '';
    }

    private function nullableString(mixed $value): ?string
    {
        $value = $this->cleanValue((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
