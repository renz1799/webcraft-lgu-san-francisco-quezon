<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItrItem extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'itr_items';

    protected $fillable = [
        'itr_id',
        'inventory_item_id',
        'line_no',
        'quantity',
        'date_acquired_snapshot',
        'inventory_item_no_snapshot',
        'description_snapshot',
        'amount_snapshot',
        'estimated_useful_life_snapshot',
        'condition_snapshot',
        'item_name_snapshot',
    ];

    protected $casts = [
        'line_no' => 'int',
        'quantity' => 'int',
        'date_acquired_snapshot' => 'date',
        'amount_snapshot' => 'decimal:2',
    ];

    public function itr(): BelongsTo
    {
        return $this->belongsTo(Itr::class, 'itr_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}



