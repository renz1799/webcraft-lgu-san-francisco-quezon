<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirItemUnitFile extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'air_item_unit_files';

    protected $fillable = [
        'air_item_unit_id',
        'driver',
        'path',
        'type',
        'is_primary',
        'position',
        'original_name',
        'mime',
        'size',
        'caption',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'position' => 'integer',
            'size' => 'integer',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(AirItemUnit::class, 'air_item_unit_id', 'id');
    }
}
