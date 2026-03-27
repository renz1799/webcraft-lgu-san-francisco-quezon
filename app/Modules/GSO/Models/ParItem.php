<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParItem extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'par_items';

    protected $fillable = [
        'par_id',
        'inventory_item_id',
        'quantity',
        'property_number_snapshot',
        'amount_snapshot',
        'unit_snapshot',
        'item_name_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'amount_snapshot' => 'decimal:2',
        ];
    }

    public function par(): BelongsTo
    {
        return $this->belongsTo(Par::class, 'par_id', 'id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }
}
