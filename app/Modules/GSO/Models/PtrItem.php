<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PtrItem extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'ptr_items';

    protected $fillable = [
        'ptr_id',
        'inventory_item_id',
        'line_no',
        'date_acquired_snapshot',
        'property_number_snapshot',
        'description_snapshot',
        'amount_snapshot',
        'condition_snapshot',
        'item_name_snapshot',
    ];

    protected $casts = [
        'line_no' => 'int',
        'date_acquired_snapshot' => 'date',
        'amount_snapshot' => 'decimal:2',
    ];

    public function ptr(): BelongsTo
    {
        return $this->belongsTo(Ptr::class, 'ptr_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
