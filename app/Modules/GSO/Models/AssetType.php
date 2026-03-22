<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetType extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'asset_types';

    protected $fillable = [
        'type_code',
        'type_name',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(AssetCategory::class, 'asset_type_id', 'id');
    }
}
