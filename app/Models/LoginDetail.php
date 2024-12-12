<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Agent\Agent;

class LoginDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'device',
        'location',
        'address',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

        /**
     * Get the detailed device name.
     */
    public function getDeviceDetailsAttribute()
    {
        $agent = new Agent();
        $agent->setUserAgent($this->device);

        $platform = $agent->platform();  // e.g., Windows, iOS, Android
        $browser = $agent->browser();   // e.g., Chrome, Safari, Edge
        $deviceType = $this->getDeviceType($agent);

        return "{$deviceType} - {$platform} - {$browser}";
    }

        /**
     * Get the device type (mobile, desktop, tablet, etc.).
     */
    protected function getDeviceType(Agent $agent)
    {
        if ($agent->isMobile()) {
            return 'Mobile';
        } elseif ($agent->isTablet()) {
            return 'Tablet';
        } elseif ($agent->isDesktop()) {
            return 'Desktop';
        }

        return 'Unknown Device';
    }

    public function getDeviceIconAttribute()
{
    $agent = new \Jenssegers\Agent\Agent();
    $agent->setUserAgent($this->device);

    if ($agent->isMobile()) {
        return 'bi-phone'; // Icon for mobile devices
    } elseif ($agent->isTablet()) {
        return 'bi-tablet'; // Icon for tablets
    } elseif ($agent->isDesktop()) {
        return 'bi-laptop'; // Icon for desktops/laptops
    }

    return 'bi-device'; // Default icon
}
    /**
     * Relationship to the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    

    
}
