<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserPermission extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'user_id', 'page', 'permission_id']; // Explicitly list the fillable fields

    public $incrementing = false; // Disable auto-incrementing for primary key
    protected $keyType = 'string'; // Use string for the primary key

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
     * Relationship with Permission.
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'id');
    }

    /**
     * Relationship with User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
