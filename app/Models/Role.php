<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory, SoftDeletes, HasUuid;

    public $incrementing = false;     // ✅ important
    protected $keyType = 'string';    // ✅ important

    protected $fillable = ['name', 'guard_name'];

    protected static function boot()
    {
        parent::boot();

        // ✅ ensures UUID is set before insert
        static::bootHasUuid();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }
}
