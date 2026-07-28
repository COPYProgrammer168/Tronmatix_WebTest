<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('provinces')->delete();
        $now = Carbon::now();
        $ppZone = 1; // Phnom Penh zone
        $provinceZone = 2; // Province zone
        DB::table('provinces')->insert([
            ['name_en' => 'Phnom Penh', 'name_kh' => 'ភ្នំពេញ', 'delivery_zone_id' => $ppZone, 'created_at' => $now, 'updated_at' => $now],
            ['name_en' => 'Siem Reap',  'name_kh' => 'សៀមរាប', 'delivery_zone_id' => $provinceZone, 'created_at' => $now, 'updated_at' => $now],
            ['name_en' => 'Battambang', 'name_kh' => 'បាត់ដំបង', 'delivery_zone_id' => $provinceZone, 'created_at' => $now, 'updated_at' => $now],
            ['name_en' => 'Sihanoukville','name_kh' => 'ព្រះសីហនុ', 'delivery_zone_id' => $provinceZone, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->command->info('✅ ProvinceSeeder: 4 provinces seeded.');
    }
}
