<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItemEventFile extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'inventory_item_event_files';

    protected $fillable = [
        'inventory_item_event_id',
        'disk',
        'path',
        'drive_file_id',
        'drive_web_view_link',
        'original_name',
        'mime_type',
        'size_bytes',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(InventoryItemEvent::class, 'inventory_item_event_id', 'id');
    }
}
