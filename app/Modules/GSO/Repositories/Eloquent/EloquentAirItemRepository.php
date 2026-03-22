<?php

namespace App\Modules\GSO\Repositories\Eloquent;

use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Repositories\Contracts\AirItemRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentAirItemRepository implements AirItemRepositoryInterface
{
    public function findOrFail(string $id): AirItem
    {
        return $this->query()->findOrFail($id);
    }

    public function listByAirId(string $airId): Collection
    {
        return $this->query()
            ->where('air_id', $airId)
            ->orderBy('item_name_snapshot')
            ->orderBy('created_at')
            ->get();
    }

    public function countByAirId(string $airId): int
    {
        return AirItem::query()
            ->where('air_id', $airId)
            ->count();
    }

    public function create(array $data): AirItem
    {
        return AirItem::query()
            ->create($data)
            ->load($this->relations());
    }

    public function save(AirItem $airItem): AirItem
    {
        $airItem->save();

        return $airItem->refresh()->load($this->relations());
    }

    public function delete(AirItem $airItem): void
    {
        $airItem->delete();
    }

    private function query(): Builder
    {
        return AirItem::query()->with($this->relations());
    }

    /**
     * @return array<string, \Closure>
     */
    private function relations(): array
    {
        return [
            'item' => fn ($query) => $query
                ->withTrashed()
                ->select([
                    'id',
                    'asset_id',
                    'item_name',
                    'description',
                    'base_unit',
                    'item_identification',
                    'tracking_type',
                    'requires_serial',
                    'is_semi_expendable',
                ]),
            'item.componentTemplates' => fn ($query) => $query
                ->select([
                    'id',
                    'item_id',
                    'line_no',
                    'name',
                    'quantity',
                    'unit',
                    'component_cost',
                    'remarks',
                ]),
            'item.unitConversions' => fn ($query) => $query
                ->select(['id', 'item_id', 'from_unit', 'multiplier']),
        ];
    }
}
