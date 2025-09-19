<?php

namespace App\Services\Contracts;

interface GeocodingServiceInterface
{
    public function reverseGeocode(float $lat, float $lng): string; // returns "City, Region, Country" or "Unknown Address"
}
