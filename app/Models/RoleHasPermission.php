<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleHasPermission extends Model
{
    use HasFactory;

    protected $table = 'role_has_permissions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['permission_id', 'role_id'];

    /**
     * Define a relationship to permissions.
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    /**
     * Define a relationship to roles.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
