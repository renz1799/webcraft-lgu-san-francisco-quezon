<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends SpatiePermission
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'guard_name'];

    /**
     * Automatically generate UUID for the id field.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Str::uuid();
            }
        });
    }

    /**
     * Define the roles relationship.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_has_permissions',
            'permission_id',
            'role_id',
            'id',
            'id'
        );
    }

    /**
     * Ensure an ID is always generated during creation.
     */
    public static function create(array $attributes = [])
    {
        if (empty($attributes['id'])) {
            $attributes['id'] = (string) \Str::uuid();
        }

        return parent::create($attributes);
    }
}
