<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'name']; // UUID and name of the permission

    public $incrementing = false; // Disable auto-incrementing for primary key
    protected $keyType = 'string'; // Use string as the primary key type

    /**
     * Boot method to generate UUID for the ID.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid(); // Generate a UUID for the 'id' field
            }
        });
    }

    /**
     * Relationship with UserPermission.
     */
    public function userPermissions()
    {
        return $this->hasMany(UserPermission::class, 'permission_id', 'id');
    }

    /**
     * Scope for querying by permission name.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }
}
