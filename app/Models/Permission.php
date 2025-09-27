<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\PermissionRegistrar;

class Permission extends SpatiePermission
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name', 'page', 'guard_name'];

    protected static function boot()
    {
        parent::boot();

        // Ensure a UUID
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Str::uuid();
            }
        });

        // Clear Spatie cache on changes (includes soft delete / restore)
        $forget = fn () => app(PermissionRegistrar::class)->forgetCachedPermissions();

        static::created($forget);
        static::updated($forget);
        static::deleted($forget);   // fires on soft delete
        static::restored($forget);  // fires on restore
        static::forceDeleted($forget);
    }

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

    // Optional: you don't need to override create(), Spatie handles fillable.
    public static function create(array $attributes = [])
    {
        if (empty($attributes['id'])) {
            $attributes['id'] = (string) \Str::uuid();
        }
        return parent::create($attributes);
    }
}
