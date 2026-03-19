<?php

namespace App\Services\Geocoding;

use App\Services\Contracts\GeocodingServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PositionstackGeocodingService implements GeocodingServiceInterface
{
    public function reverseGeocode(float $latitude, float $longitude): string
    {
        try {
            $response = Http::timeout(5)
                ->get('https://api.positionstack.com/v1/reverse', [
                    'access_key' => config('services.positionstack.key'),
                    'query' => $latitude . ',' . $longitude,
                    'limit' => 1,
                ])
                ->throw();

            $data = $response->json('data.0');

            if (! is_array($data)) {
                return 'Unknown Address';
            }

            $parts = array_filter([
                $data['locality'] ?? null,
                $data['region'] ?? null,
                $data['country'] ?? null,
            ]);

            return $parts !== []
                ? implode(', ', $parts)
                : 'Unknown Address';
        } catch (\Throwable $e) {
            Log::warning('Positionstack reverse geocoding failed.', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'message' => $e->getMessage(),
            ]);

            return 'Unknown Address';
        }
    }
}