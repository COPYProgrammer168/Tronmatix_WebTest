<?php

namespace Database\Seeders;

use App\Models\Banner;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        Banner::insert([
            [
                'title' => 'WHITE SET',
                'subtitle' => 'HIGH END PC BUILD',
                'badge' => 'NEW ARRIVAL',
                'image' => 'https://picsum.photos/seed/banner1/1400/500',
                'order' => 1,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'PC BUILD BUDGET 3K FOR GAMING',
                'subtitle' => 'AMD RYZEN 9 9950X3D RTX5080',
                'badge' => 'HOT DEAL',
                'image' => 'https://picsum.photos/seed/banner2/1400/500',
                'order' => 2,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'GAMING MONITOR SALE',
                'subtitle' => 'UP TO 30% OFF — LIMITED TIME',
                'badge' => 'SALE',
                'image' => 'https://picsum.photos/seed/banner3/1400/500',
                'order' => 3,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->command->info('✅ Banners seeded: '.Banner::count());
    }
}
