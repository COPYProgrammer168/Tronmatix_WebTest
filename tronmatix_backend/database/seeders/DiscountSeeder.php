<?php

// database/seeders/DiscountSeeder.php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $discounts = [];

        for ($i = 1; $i <= 10; $i++) {
            $discounts[] = [
                'code' => 'RANDOM' . $i . rand(10, 99),
                'type' => rand(0, 1) ? 'percentage' : 'fixed',
                'value' => rand(5, 50),
                'min_order' => rand(0, 100),
                'max_uses' => rand(10, 100),
                'used_count' => 0,
                'categories' => null,
                'expires_at' => $now->copy()->addMonths(rand(1, 6)),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('discounts')->insert($discounts);

        $this->command->info('✅  DiscountSeeder: '.count($discounts).' codes inserted.');
    }
}
