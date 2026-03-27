<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WmrItem extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'wmr_items';

    protected $fillable = [
        'wmr_id',
        'inventory_item_id',
        'line_no',
        'quantity',
        'unit_snapshot',
        'description_snapshot',
        'item_name_snapshot',
        'reference_no_snapshot',
        'date_acquired_snapshot',
        'acquisition_cost_snapshot',
        'condition_snapshot',
        'disposal_method',
        'transfer_entity_name',
        'official_receipt_no',
        'official_receipt_date',
        'official_receipt_amount',
    ];

    protected $casts = [
        'line_no' => 'int',
        'quantity' => 'int',
        'date_acquired_snapshot' => 'date',
        'acquisition_cost_snapshot' => 'decimal:2',
        'official_receipt_date' => 'date',
        'official_receipt_amount' => 'decimal:2',
    ];

    public function wmr(): BelongsTo
    {
        return $this->belongsTo(Wmr::class, 'wmr_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}

