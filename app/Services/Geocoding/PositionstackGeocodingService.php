<?php

namespace App\Services\Geocoding;

use App\Services\Contracts\GeocodingServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PositionstackGeocodingService implements GeocodingServiceInterface
{
    public function reverseGeocode(float $lat, float $lng): string
    {
        try {
            $response = Http::retry(2, 200)
                ->timeout(5)
                ->get('http://api.positionstack.com/v1/reverse', [
                    'access_key' => config('services.positionstack.key'),
                    'query'      => "{$lat},{$lng}",
                    'limit'      => 1,
                ]);

            if ($response->ok() && ($data = $response->json()['data'][0] ?? null)) {
                $cityOrCounty = $data['locality'] ?? $data['county'] ?? 'Unknown City';
                $province     = $data['region']   ?? 'Unknown Province';
                $country      = $data['country']  ?? 'Unknown Country';
                return "{$cityOrCounty}, {$province}, {$country}";
            }
        } catch (\Throwable $e) {
            Log::error('Positionstack reverseGeocode failed', ['error' => $e->getMessage()]);
        }

        return 'Unknown Address';
    }
}
