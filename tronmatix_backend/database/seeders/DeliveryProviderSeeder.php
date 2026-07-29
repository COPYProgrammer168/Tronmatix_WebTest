<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryProviderSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('delivery_providers')->delete();

        // Resolve zone IDs dynamically
        $ppZone = DB::table('delivery_zones')->where('slug', 'phnom_penh')->value('id');
        $provinceZone = DB::table('delivery_zones')->where('slug', 'province')->value('id');

        if (!$ppZone || !$provinceZone) {
            $this->command->error('❌ Delivery zones not found. Run DeliveryZoneSeeder first.');
            return;
        }

        $now = Carbon::now();
        DB::table('delivery_providers')->insert([
            ['delivery_zone_id' => $ppZone,      'name' => 'Naga Express',   'logo' => null, 'fee' => 2.00,  'estimated_time' => '30–60 min', 'is_active' => true,  'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['delivery_zone_id' => $ppZone,      'name' => 'Speed Post',     'logo' => null, 'fee' => 1.50,  'estimated_time' => '1–2 days',  'is_active' => true,  'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['delivery_zone_id' => $ppZone,      'name' => 'VET Express',    'logo' => null, 'fee' => 3.00,  'estimated_time' => '2–4 hours', 'is_active' => false, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['delivery_zone_id' => $provinceZone, 'name' => 'Cambodia Post',  'logo' => null, 'fee' => null,  'estimated_time' => '3–7 days',  'is_active' => true,  'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['delivery_zone_id' => $provinceZone, 'name' => 'Express Courier','logo' => null, 'fee' => 5.00,  'estimated_time' => '1–3 days',  'is_active' => true,  'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->command->info('✅ DeliveryProviderSeeder: 5 providers seeded.');
    }
}
