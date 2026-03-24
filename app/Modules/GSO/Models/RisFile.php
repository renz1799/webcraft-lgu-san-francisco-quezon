<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RisFile extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'ris_files';

    protected $fillable = [
        'ris_id',
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

    public function ris(): BelongsTo
    {
        return $this->belongsTo(Ris::class, 'ris_id', 'id');
    }
}
