<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;            
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;                              

    // No need to mass-assign 'id' anymore; let the trait set it
    protected $fillable = ['name', 'guard_name'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_permissions',
            'role_id',
            'permission_id',
            'id',
            'id'
        );
    }
}
