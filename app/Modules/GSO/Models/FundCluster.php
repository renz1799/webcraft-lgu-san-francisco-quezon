<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundCluster extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'fund_clusters';

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function fundSources(): HasMany
    {
        return $this->hasMany(FundSource::class, 'fund_cluster_id', 'id');
    }
}
