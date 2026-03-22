<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EloquentItemRepository implements ItemRepositoryInterface
{
    public function findOrFail(string $id, bool $withTrashed = false): Item
    {
        $query = Item::query()->with([
            'asset' => fn ($assetQuery) => $assetQuery
                ->withTrashed()
                ->select(['id', 'asset_code', 'asset_name']),
            'unitConversions' => fn ($conversionQuery) => $conversionQuery
                ->select(['id', 'item_id', 'from_unit', 'multiplier']),
        ]);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function paginateForTable(array $filters, int $page = 1, int $size = 15): LengthAwarePaginator
    {
        $query = Item::query()
            ->with([
                'asset' => fn ($assetQuery) => $assetQuery
                    ->withTrashed()
                    ->select(['id', 'asset_code', 'asset_name']),
            ]);

        $archived = $this->resolveArchivedMode($filters);
        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        $assetId = trim((string) ($filters['asset_id'] ?? ''));
        $trackingType = trim((string) ($filters['tracking_type'] ?? ''));
        $requiresSerial = trim((string) ($filters['requires_serial'] ?? ''));
        $semiExpendable = trim((string) ($filters['is_semi_expendable'] ?? ''));

        if ($archived === 'all') {
            $query->withTrashed();
        } elseif ($archived === 'archived') {
            $query->onlyTrashed();
        }

        if ($assetId !== '') {
            $query->where('asset_id', $assetId);
        }

        if ($trackingType !== '') {
            $query->where('tracking_type', $trackingType);
        }

        if (in_array($requiresSerial, ['0', '1'], true)) {
            $query->where('requires_serial', $requiresSerial === '1');
        }

        if (in_array($semiExpendable, ['0', '1'], true)) {
            $query->where('is_semi_expendable', $semiExpendable === '1');
        }

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('item_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('base_unit', 'like', "%{$search}%")
                    ->orWhere('item_identification', 'like', "%{$search}%")
                    ->orWhere('major_sub_account_group', 'like', "%{$search}%")
                    ->orWhereHas('asset', function (Builder $assetQuery) use ($search) {
                        $assetQuery->withTrashed()
                            ->where('asset_code', 'like', "%{$search}%")
                            ->orWhere('asset_name', 'like', "%{$search}%");
                    });
            });
        }

        return $query
            ->orderBy('item_name')
            ->paginate(
                perPage: max(1, min($size, 100)),
                columns: ['*'],
                pageName: 'page',
                page: max(1, $page),
            );
    }

    public function create(array $data): Item
    {
        return Item::query()->create($data)->load(['asset', 'unitConversions']);
    }

    public function save(Item $item): Item
    {
        $item->save();

        return $item->refresh()->load(['asset', 'unitConversions']);
    }

    public function delete(Item $item): void
    {
        $item->delete();
    }

    public function restore(Item $item): void
    {
        $item->restore();
    }

    private function resolveArchivedMode(array $filters): string
    {
        $mode = trim((string) ($filters['archived'] ?? $filters['status'] ?? 'active'));

        if (in_array($mode, ['active', 'archived', 'all'], true)) {
            return $mode;
        }

        return 'active';
    }
}
