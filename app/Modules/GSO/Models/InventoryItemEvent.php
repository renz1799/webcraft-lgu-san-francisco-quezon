<?php

namespace App\Modules\GSO\Models;

use App\Core\Models\Department;
use App\Core\Models\User;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItemEvent extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'inventory_item_events';

    protected $fillable = [
        'inventory_item_id',
        'department_id',
        'performed_by_user_id',
        'event_type',
        'event_date',
        'qty_in',
        'qty_out',
        'amount_snapshot',
        'unit_snapshot',
        'office_snapshot',
        'officer_snapshot',
        'status',
        'condition',
        'person_accountable',
        'notes',
        'reference_type',
        'reference_no',
        'reference_id',
        'fund_source_id',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'qty_in' => 'integer',
            'qty_out' => 'integer',
            'amount_snapshot' => 'decimal:2',
        ];
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by_user_id', 'id');
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(InventoryItemEventFile::class, 'inventory_item_event_id', 'id');
    }
}
