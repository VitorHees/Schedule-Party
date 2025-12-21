<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait HandlesGeocoding
{
    /**
     * Geocode an address string to coordinates [lon, lat].
     */
    public function geocodeLocation(?string $address): ?array
    {
        if (empty($address)) {
            return null;
        }

        try {
            // Use config for email to avoid hardcoding (Security)
            $email = config('services.osm.email', 'app@example.com');

            $response = Http::withHeaders([
                'User-Agent' => 'SchedulePartyApp/1.0 (' . $email . ')',
                'Referer'    => config('app.url')
            ])->timeout(5)->get("https://nominatim.openstreetmap.org/search", [
                'q' => $address,
                'format' => 'json',
                'limit' => 1
            ]);

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];
                return [$data['lon'], $data['lat']];
            }
        } catch (\Exception $e) {
            // Fail silently or log error if needed
            return null;
        }

        return null;
    }
}
