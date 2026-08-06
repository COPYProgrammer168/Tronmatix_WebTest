<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeds the delivery providers serving Cambodia, each with per-division rows
// (delivery_provider_zones: phnom_penh + province) so the checkout/stepper can
// resolve zone-specific fee + ETA. The legacy flat delivery_providers rows are
// attached to the "Phnom Penh" delivery_zone for backward compatibility.
//
// LOGOS: J&T has a verified hotlinkable Wikimedia URL. The rest are left null so
// the frontend shows the truck placeholder and the admin can upload real logos
// in Dashboard → Delivery Providers → Edit. Fees/ETAs are editable placeholders.
class DeliveryProviderSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $ppZoneId = DB::table('delivery_zones')->where('slug', 'phnom_penh')->value('id');

        $providers = [
            // ── Global / express couriers ───────────────────────────────────
            'J&T Express' => [
                // Verified Wikimedia Commons SVG (tested 200 OK).
                'logo' => 'https://commons.wikimedia.org/wiki/Special:FilePath/J%26T_Express_logo.svg?width=200',
                'phnom_penh' => ['fee' => 1.50, 'estimated_time' => '20-40 min'],
                'province'   => ['fee' => 3.50, 'estimated_time' => '1-2 days'],
                'sort' => 1,
            ],
            'Ninja Van' => [
                'logo' => null,
                'phnom_penh' => ['fee' => 1.00, 'estimated_time' => 'Half-day'],
                'province'   => ['fee' => 3.00, 'estimated_time' => '1-2 days'],
                'sort' => 2,
            ],
            'DHL Express (Cambodia)' => [
                'logo' => null,
                'phnom_penh' => ['fee' => 5.00, 'estimated_time' => 'Same day'],
                'province'   => ['fee' => 12.00, 'estimated_time' => '2-4 days'],
                'sort' => 3,
            ],

            // ── Cambodia local / Khmer couriers ─────────────────────────────
            'Virak Buntham Express' => [
                'logo' => null,
                'phnom_penh' => ['fee' => 1.00, 'estimated_time' => '30-60 min'],
                'province'   => ['fee' => 3.00, 'estimated_time' => '1-3 days'],
                'sort' => 4,
            ],
            'Jabber Express' => [
                'logo' => null,
                'phnom_penh' => ['fee' => 1.20, 'estimated_time' => '30-60 min'],
                'province'   => ['fee' => 3.50, 'estimated_time' => '1-3 days'],
                'sort' => 5,
            ],
            'BEST Express Cambodia' => [
                'logo' => null,
                'phnom_penh' => ['fee' => 1.00, 'estimated_time' => 'Half-day'],
                'province'   => ['fee' => 2.80, 'estimated_time' => '2-3 days'],
                'sort' => 6,
            ],
            'Grab Express' => [
                'logo' => null,
                'phnom_penh' => ['fee' => 1.80, 'estimated_time' => '15-40 min'],
                'province'   => ['fee' => null, 'estimated_time' => 'In-app quote'],
                'sort' => 7,
            ],
            'Lalamove (Cambodia)' => [
                'logo' => null,
                'phnom_penh' => ['fee' => 2.00, 'estimated_time' => '15-45 min'],
                'province'   => ['fee' => null, 'estimated_time' => 'In-app quote'],
                'sort' => 8,
            ],

            // ── In-house delivery (local truck) ─────────────────────────────
            'ក្រុមហ៊ុនដឹកជញ្ជូនផ្ទាល់ (In-house)' => [
                'logo' => null,
                'phnom_penh' => ['fee' => 1.20, 'estimated_time' => '2-4 hours'],
                'province'   => ['fee' => null, 'estimated_time' => 'Negotiable'],
                'sort' => 9,
            ],
        ];

        foreach ($providers as $name => $config) {
            $provider = DB::table('delivery_providers')->where('name', $name)->first();

            if (! $provider) {
                $providerId = DB::table('delivery_providers')->insertGetId([
                    'delivery_zone_id' => $ppZoneId,
                    'name'             => $name,
                    'logo'             => $config['logo'],
                    'fee'              => null,
                    'estimated_time'   => null,
                    'is_active'        => true,
                    'sort_order'       => $config['sort'] ?? 0,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            } else {
                $providerId = $provider->id;
                // Update logo if we have one and the provider currently has none.
                if ($config['logo'] && empty($provider->logo)) {
                    DB::table('delivery_providers')
                        ->where('id', $providerId)
                        ->update(['logo' => $config['logo']]);
                }
            }

            foreach (['phnom_penh', 'province'] as $zone) {
                $exists = DB::table('delivery_provider_zones')
                    ->where('delivery_provider_id', $providerId)
                    ->where('zone', $zone)
                    ->exists();

                if (! $exists && isset($config[$zone])) {
                    DB::table('delivery_provider_zones')->insert([
                        'delivery_provider_id' => $providerId,
                        'zone'                 => $zone,
                        'fee'                  => $config[$zone]['fee'],
                        'estimated_time'       => $config[$zone]['estimated_time'],
                        'created_at'           => $now,
                        'updated_at'           => $now,
                    ]);
                }
            }
        }

        $this->command->info('✅ DeliveryProviderSeeder: Cambodia providers + per-division (Phnom Penh / Province) fees & ETA.');
    }
}