<?php

namespace App\Services;

use App\Models\DeliveryFeeZone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Distance-based delivery fee calculator.
//
// Flow:
//   1. Reverse-geocode the customer's { lat, lng } via Nominatim to get a
//      province name.
//   2. Match that province against delivery_fee_zones.province_match (fuzzy).
//      Fall back to the zone with province_match = NULL if nothing matches.
//   3. Compute haversine distance from the shop origin, scaled by road_factor.
//   4. If max_distance_km is exceeded → mark the result out_of_range.
//   5. fee = base_fee + max(0, distance - free_km) * per_km_rate.
//
// If geocoding fails/times out we fall back to straight-line distance + the
// fallback province zone so checkout never breaks.
class DeliveryFeeCalculator
{
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/reverse';
    private const GEOCODE_CACHE_KEY = 'delivery_fee:geo:{lat},{lng}';
    private const GEOCODE_CACHE_HOURS = 24;

    /**
     * @return array<string,mixed>
     */
    public function calculate(float $customerLat, float $customerLng): array
    {
        $zone = $this->resolveZone($customerLat, $customerLng);
        $distance = $this->haversine(
            (float) config('services.shop.origin_lat', 0),
            (float) config('services.shop.origin_lng', 0),
            $customerLat,
            $customerLng
        ) * (float) ($zone['road_factor'] ?? 1.0);

        $outOfRange = false;
        if (($max = $zone['max_distance_km']) !== null && $distance > (float) $max) {
            $outOfRange = true;
        }

        $billableKm = max(0, $distance - (float) ($zone['free_km'] ?? 0));
        $fee = $outOfRange
            ? 0.0
            : (float) $zone['base_fee'] + $billableKm * (float) $zone['per_km_rate'];

        return [
            'zone_name'    => $zone['zone_name'] ?? 'Default',
            'distance_km'  => round($distance, 2),
            'delivery_fee' => round($fee, 2),
            'out_of_range' => $outOfRange,
        ];
    }

    // ── Zone resolution ───────────────────────────────────────────────────────

    /**
     * Pick an active fee zone for a customer location.
     *
     * @return array{zone_name:string,province_match:?string,base_fee:float,free_km:float,per_km_rate:float,max_distance_km:?float,road_factor:float}
     */
    private function resolveZone(float $lat, float $lng): array
    {
        $province = null;
        try {
            $province = $this->reverseGeocodeProvince($lat, $lng);
        } catch (\Throwable $e) {
            Log::warning('[DeliveryFee] Geocoding failed; using fallback zone: ' . $e->getMessage());
        }

        $zone = null;
        if ($province) {
            $zone = $this->matchByProvince($province);
        }

        if (! $zone) {
            $zone = DeliveryFeeZone::query()
                ->active()
                ->default()
                ->first();
        }

        return $this->zoneToArray($zone);
    }

    /**
     * Fuzzy-match a geocoded province name against an active fee zone.
     */
    private function matchByProvince(string $province): ?DeliveryFeeZone
    {
        return DeliveryFeeZone::query()
            ->active()
            ->whereNotNull('province_match')
            ->get()
            ->first(function (DeliveryFeeZone $zone) use ($province) {
                $p = mb_strtolower($province);
                $z = mb_strtolower($zone->province_match ?? '');
                // Match either direction so "Kampong Speu Province" ↔ "Kampong Speu" both hit.
                return $z !== '' && (str_contains($p, $z) || str_contains($z, $p));
            });
    }

    /**
     * @return array{zone_name:string,province_match:?string,base_fee:float,free_km:float,per_km_rate:float,max_distance_km:?float,road_factor:float}
     */
    private function zoneToArray(?DeliveryFeeZone $zone): array
    {
        return [
            'zone_name'       => $zone?->zone_name ?? 'Default',
            'province_match'  => $zone?->province_match ?? null,
            'base_fee'        => (float) ($zone?->base_fee ?? 0),
            'free_km'         => (float) ($zone?->free_km ?? 0),
            'per_km_rate'     => (float) ($zone?->per_km_rate ?? 0),
            'max_distance_km' => $zone?->max_distance_km !== null
                ? (float) $zone->max_distance_km
                : null,
            'road_factor'     => (float) ($zone?->road_factor ?? 1.0),
        ];
    }

    // ── Geocoding ─────────────────────────────────────────────────────────────

    /**
     * Reverse-geocode lat/lng → province name ('state' or 'county' from Nominatim).
     * Cached 24h keyed by rounded coords to avoid repeat calls per location.
     *
     * @throws \Throwable
     */
    private function reverseGeocodeProvince(float $lat, float $lng): ?string
    {
        $key = $this->cacheKey($lat, $lng);
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached;
        }

        $province = $this->fetchProvinceFromNominatim($lat, $lng);

        if ($province) {
            Cache::put($key, $province, now()->addHours(self::GEOCODE_CACHE_HOURS));
        }

        return $province;
    }

    private function fetchProvinceFromNominatim(float $lat, float $lng): ?string
    {
        $response = Http::timeout(2)
            ->withHeaders([
                'User-Agent' => 'TronmatixStore/1.0 (https://tronmatixcomputer.com; contact@tronmatixcomputer.com)',
                'Accept-language' => 'en',
            ])
            ->acceptJson()
            ->get(self::NOMINATIM_URL, [
                'format' => 'jsonv2',
                'lat' => $lat,
                'lon' => $lng,
                'zoom' => 10,
            ]);

        if (! $response->successful()) {
            Log::warning('DeliveryFee: Nominatim returned ' . $response->status());
            return null;
        }

        $data = $response->json();
        $address = $data['address'] ?? [];

        // Nominatim for Cambodia typically exposes 'state' and/or 'state_district'.
        // Prefer 'state', then top-level region fields.
        return $address['state']
            ?? $address['state_district']
            ?? $address['region']
            ?? $address['province']   // current maps province → '__'
            ?? null;
    }

    private function cacheKey(float $lat, float $lng): string
    {
        // Round to ~3 decimal places (≈110m) so nearby pin moves reuse the cached geocode.
        return str_replace(
            ['{lat}', '{lng}'],
            [round($lat, 3), round($lng, 3)],
            self::GEOCODE_CACHE_KEY
        );
    }

    // ── Distance ──────────────────────────────────────────────────────────────

    /**
     * Great-circle (haversine) distance in km between two lat/lng points.
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}