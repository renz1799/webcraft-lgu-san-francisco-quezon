<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItemComponent extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'inventory_item_components';

    protected $fillable = [
        'inventory_item_id',
        'line_no',
        'name',
        'quantity',
        'unit',
        'component_cost',
        'serial_number',
        'condition',
        'is_present',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'line_no' => 'integer',
            'quantity' => 'integer',
            'component_cost' => 'decimal:2',
            'is_present' => 'boolean',
        ];
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }
}
