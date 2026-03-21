<?php

namespace App\Core\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jenssegers\Agent\Agent;

class LoginDetail extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'module_id','user_id','email','ip_address','device','location','address',
        'latitude','longitude','success','reason',
    ];

    protected $casts = [
        'success' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected static function boot()
    {
        parent::boot();
        static::bootHasUuid();
    }

    // Accessors (kept for UI convenience)
    public function getDeviceDetailsAttribute(): string
    {
        $agent = new Agent();
        $agent->setUserAgent($this->device);

        $platform   = $agent->platform();
        $browser    = $agent->browser();
        $deviceType = $this->detectDeviceType($agent);

        return "{$deviceType} - {$platform} - {$browser}";
    }

    public function getDeviceIconAttribute(): string
    {
        $agent = new Agent();
        $agent->setUserAgent($this->device);

        if ($agent->isMobile())  return 'bi-phone';
        if ($agent->isTablet())  return 'bi-tablet';
        if ($agent->isDesktop()) return 'bi-laptop';

        return 'bi-device';
    }

    protected function detectDeviceType(Agent $agent): string
    {
        if ($agent->isMobile())  return 'Mobile';
        if ($agent->isTablet())  return 'Tablet';
        if ($agent->isDesktop()) return 'Desktop';
        return 'Unknown Device';
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
