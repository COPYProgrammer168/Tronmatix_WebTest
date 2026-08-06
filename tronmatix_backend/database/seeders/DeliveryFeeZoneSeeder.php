<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Seeds the distance-based delivery fee tiers (delivery_fee_zones).
// NOTE: These are PLACEHOLDER rates the shop owner will adjust later.
class DeliveryFeeZoneSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('delivery_fee_zones')->insert([
            [
                'zone_name'       => 'Phnom Penh',
                'province_match'  => 'Phnom Penh',
                'base_fee'        => 1.00,
                'free_km'         => 3,
                'per_km_rate'     => 0.15,
                'max_distance_km' => 60,
                'road_factor'     => 1.3,
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'zone_name'       => 'Other Provinces',
                'province_match'  => null, // fallback — catches anything not matched above
                'base_fee'        => 2.50,
                'free_km'         => 0,
                'per_km_rate'     => 0.20,
                'max_distance_km' => 200,
                'road_factor'     => 1.2,
                'is_active'       => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ]);

        $this->command->info('✅ DeliveryFeeZoneSeeder: 2 fee zones (Phnom Penh + Other Provinces fallback).');
    }
}
