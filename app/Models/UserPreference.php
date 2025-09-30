<?php
// app/Models/UserPreference.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'user_id';
    protected $keyType = 'string';
    protected $fillable = ['user_id','theme_style'];
    protected $casts = ['theme_style' => 'array'];
}
