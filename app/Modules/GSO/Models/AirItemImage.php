<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirItemImage extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'air_item_images';

    protected $fillable = [
        'air_item_id',
        'storage_provider',
        'storage_disk',
        'storage_path',
        'external_file_id',
        'original_name',
        'stored_name',
        'mime_type',
        'size_bytes',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    public function airItem(): BelongsTo
    {
        return $this->belongsTo(AirItem::class, 'air_item_id', 'id');
    }
}
