<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate first so re-seeding never creates duplicates
        DB::table('provinces')->delete();

        $now = Carbon::now();

        // Resolve zone IDs via DB query so we don't hard-code numeric IDs
        $phnomPenhZone = DB::table('delivery_zones')->where('slug', 'phnom_penh')->value('id');
        $provinceZone = DB::table('delivery_zones')->where('slug', 'province')->value('id');

        $provinces = [
            ['name_en' => 'Phnom Penh', 'name_kh' => 'ភ្នំពេញ', 'delivery_zone_id' => $phnomPenhZone],
            ['name_en' => 'Banteay Meanchey', 'name_kh' => 'បន្ទាយមានជ័យ', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Battambang', 'name_kh' => 'បាត់ដំបង', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Kampong Cham', 'name_kh' => 'កំពង់ចាម', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Kampong Chhnang', 'name_kh' => 'កំពង់ឆ្នាំង', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Kampong Speu', 'name_kh' => 'កំពង់ស្ពឺ', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Kampong Thom', 'name_kh' => 'កំពង់ធំ', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Kampot', 'name_kh' => 'កំពត', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Kandal', 'name_kh' => 'កណ្តាល', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Kep', 'name_kh' => 'កែប', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Koh Kong', 'name_kh' => 'កោះកុង', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Kratie', 'name_kh' => 'ក្រចេះ', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Mondulkiri', 'name_kh' => 'មណ្ឌលគិរី', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Oddar Meanchey', 'name_kh' => 'ឧត្តរមានជ័យ', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Pailin', 'name_kh' => 'ប៉ៃលិន', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Preah Vihear', 'name_kh' => 'ព្រះវិហារ', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Prey Veng', 'name_kh' => 'ព្រៃវែង', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Pursat', 'name_kh' => 'ពោធិ៍សាត់', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Ratanakiri', 'name_kh' => 'រតនគិរី', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Siem Reap', 'name_kh' => 'សៀមរាប', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Preah Sihanouk', 'name_kh' => 'ព្រះសីហនុ', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Stung Treng', 'name_kh' => 'ស្ទឹងត្រែង', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Svay Rieng', 'name_kh' => 'ស្វាយរៀង', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Takeo', 'name_kh' => 'តាកែវ', 'delivery_zone_id' => $provinceZone],
            ['name_en' => 'Tboung Khmum', 'name_kh' => 'ត្បូងឃ្មុំ', 'delivery_zone_id' => $provinceZone],
        ];

        $rows = [];
        foreach ($provinces as $province) {
            $rows[] = [
                'name_en' => $province['name_en'],
                'name_kh' => $province['name_kh'],
                'delivery_zone_id' => $province['delivery_zone_id'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('provinces')->insert($rows);

        $this->command->info('✅ ProvinceSeeder: ' . count($rows) . ' provinces (' . count($rows) . ' Khmer names, ' . count($rows) . ' province names, all zone-mapped).');
    }
}