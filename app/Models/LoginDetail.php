<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;

class LoginDetail extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'ip_address',
        'device',
        'location', // google maps url
        'address',  // human readable (reverse geocoded)
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude'  => 'decimal:8',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
