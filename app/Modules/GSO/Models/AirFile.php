<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirFile extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'air_files';

    protected $fillable = [
        'air_id',
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

    public function air(): BelongsTo
    {
        return $this->belongsTo(Air::class, 'air_id', 'id');
    }
}
