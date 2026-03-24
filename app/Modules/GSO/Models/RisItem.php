<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RisItem extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'ris_items';

    protected $fillable = [
        'ris_id',
        'item_id',
        'line_no',
        'stock_no_snapshot',
        'description_snapshot',
        'unit_snapshot',
        'qty_requested',
        'qty_issued',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'line_no' => 'integer',
            'qty_requested' => 'integer',
            'qty_issued' => 'integer',
        ];
    }

    public function ris(): BelongsTo
    {
        return $this->belongsTo(Ris::class, 'ris_id', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
