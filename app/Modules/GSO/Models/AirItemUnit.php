<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirItemUnit extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'air_item_units';

    protected $fillable = [
        'air_item_id',
        'inventory_item_id',
        'brand',
        'model',
        'serial_number',
        'property_number',
        'condition_status',
        'condition_notes',
        'drive_folder_id',
    ];

    public function airItem(): BelongsTo
    {
        return $this->belongsTo(AirItem::class, 'air_item_id', 'id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }

    public function inventoryRecord(): HasOne
    {
        return $this->hasOne(InventoryItem::class, 'air_item_unit_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(AirItemUnitFile::class, 'air_item_unit_id', 'id')
            ->orderByDesc('is_primary')
            ->orderBy('position')
            ->orderByDesc('created_at');
    }

    public function components(): HasMany
    {
        return $this->hasMany(AirItemUnitComponent::class, 'air_item_unit_id', 'id')
            ->orderBy('line_no')
            ->orderBy('name');
    }
}
