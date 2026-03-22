<?php

namespace App\Modules\GSO\Models;

use App\Core\Models\Department;
use App\Models\Concerns\HasUuid;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'inventory_items';

    protected $fillable = [
        'item_id',
        'air_item_unit_id',
        'department_id',
        'fund_source_id',
        'property_number',
        'acquisition_date',
        'acquisition_cost',
        'description',
        'quantity',
        'unit',
        'stock_number',
        'service_life',
        'is_ics',
        'accountable_officer',
        'accountable_officer_id',
        'custody_state',
        'status',
        'condition',
        'brand',
        'model',
        'serial_number',
        'po_number',
        'drive_folder_id',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'quantity' => 'integer',
            'service_life' => 'integer',
            'is_ics' => 'boolean',
        ];
    }

    public function setAcquisitionDateAttribute(mixed $value): void
    {
        if ($value === null || trim((string) $value) === '') {
            $this->attributes['acquisition_date'] = null;

            return;
        }

        $date = $value instanceof CarbonInterface
            ? $value
            : Carbon::parse((string) $value);

        $this->attributes['acquisition_date'] = $date->toDateString();
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    public function fundSource(): BelongsTo
    {
        return $this->belongsTo(FundSource::class, 'fund_source_id', 'id');
    }

    public function accountableOfficerRelation(): BelongsTo
    {
        return $this->belongsTo(AccountableOfficer::class, 'accountable_officer_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(InventoryItemFile::class, 'inventory_item_id', 'id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(InventoryItemEvent::class, 'inventory_item_id', 'id');
    }

    public function latestEvent(): HasOne
    {
        return $this->hasOne(InventoryItemEvent::class, 'inventory_item_id', 'id')
            ->latestOfMany('event_date');
    }

    public function components(): HasMany
    {
        return $this->hasMany(InventoryItemComponent::class, 'inventory_item_id', 'id')
            ->orderBy('line_no')
            ->orderBy('name');
    }
}
