<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelHasPermission extends Model
{
    use HasFactory;

    protected $table = 'model_has_permissions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['permission_id', 'model_type', 'model_id'];

    /**
     * Define a relationship to permissions.
     */
    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    /**
     * Define a polymorphic relationship to models.
     */
    public function model()
    {
        return $this->morphTo();
    }
}
