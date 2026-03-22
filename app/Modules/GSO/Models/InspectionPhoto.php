<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InspectionPhoto extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'inspection_photos';

    protected $fillable = [
        'inspection_id',
        'driver',
        'path',
        'original_name',
        'mime',
        'size',
        'caption',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class, 'inspection_id', 'id');
    }
}
