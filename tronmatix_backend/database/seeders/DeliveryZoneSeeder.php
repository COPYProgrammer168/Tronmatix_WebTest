<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('delivery_zones')->delete();
        $now = Carbon::now();
        DB::table('delivery_zones')->insert([
            ['name' => 'Phnom Penh', 'slug' => 'phnom_penh', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Province',    'slug' => 'province',    'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->command->info('✅ DeliveryZoneSeeder: 2 zones (Phnom Penh + Province).');
    }
}
