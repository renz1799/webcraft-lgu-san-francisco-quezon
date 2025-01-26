<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends SpatieRole
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'guard_name'];

    /**
     * Override the permissions relationship to match the expected signature.
     */
    public function permissions(): BelongsToMany
    {
        // Use default Spatie relationship definition
        return parent::permissions();
    }
}
