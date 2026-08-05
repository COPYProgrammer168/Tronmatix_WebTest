<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IpLocationService
{
    private const CACHE_TTL = 60 * 24; // 24 hours — IP-to-location rarely changes

    /**
     * Resolve an IP address to location data.
     *
     * @return array{country: string, region: string, city: string, isp: string}|null
     */
    public static function resolve(string $ip): ?array
    {
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'localhost') {
            return [
                'country' => 'Localhost',
                'region'  => 'Local',
                'city'    => 'Localhost',
                'isp'     => 'Local',
            ];
        }

        try {
            return Cache::remember("ip_location:{$ip}", self::CACHE_TTL, function () use ($ip) {
                $response = Http::timeout(5)
                    ->withoutVerifying()
                    ->get("http://ip-api.com/json/{$ip}", [
                        'fields' => 'status,country,regionName,city,isp,query',
                    ]);

                if (! $response->successful()) {
                    return null;
                }

                $data = $response->json();

                if (($data['status'] ?? '') !== 'success') {
                    return null;
                }

                return [
                    'country' => $data['country'] ?? null,
                    'region'  => $data['regionName'] ?? null,
                    'city'    => $data['city'] ?? null,
                    'isp'     => $data['isp'] ?? null,
                ];
            });
        } catch (\Throwable $e) {
            Log::warning('IP location lookup failed: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Format location as a human-readable string.
     */
    public static function format(array $location): string
    {
        $parts = array_filter([
            $location['city']    ?? null,
            $location['region']  ?? null,
            $location['country'] ?? null,
        ], fn($v) => !empty($v));

        return !empty($parts) ? implode(', ', $parts) : 'Unknown';
    }
}
