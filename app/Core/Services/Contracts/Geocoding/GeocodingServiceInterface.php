<?php

namespace App\Core\Services\Contracts\Geocoding;

interface GeocodingServiceInterface
{
    public function reverseGeocode(float $lat, float $lng): string; // returns "City, Region, Country" or "Unknown Address"
}
