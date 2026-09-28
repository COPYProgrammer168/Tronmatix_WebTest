<?php

// database/seeders/DiscountSeeder.php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds stable discount codes (no random suffix — re-seeding stays idempotent
 * against the discounts_code_unique index). Codes are the System-wide ones the
 * OrderSeeder applies to ~40% of seeded orders.
 */
class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $discounts = [
            ['code' => 'SAVE10', 'kind' => 'code', 'type' => 'percentage', 'value' => 10, 'min_order' => 20,   'max_uses' => 1000,  'expires_in_months' => 6],
            ['code' => 'SAVE20', 'kind' => 'code', 'type' => 'percentage', 'value' => 20, 'min_order' => 50,   'max_uses' => 1000,  'expires_in_months' => 4],
            ['code' => 'WELCOME5', 'kind' => 'code', 'type' => 'fixed',    'value' => 5,  'min_order' => 0,    'max_uses' => 2000,  'expires_in_months' => 12],
            ['code' => 'FREESHIP', 'kind' => 'code', 'type' => 'fixed',    'value' => 5,  'min_order' => 100,  'max_uses' => 500,   'expires_in_months' => 3],
            ['code' => 'VIP15', 'kind' => 'code',   'type' => 'percentage', 'value' => 15, 'min_order' => 80,  'max_uses' => 800,   'expires_in_months' => 5],
        ];

        $rows = [];
        foreach ($discounts as $d) {
            $rows[] = [
                'code'         => $d['code'],
                'kind'         => $d['kind'],
                'type'         => $d['type'],
                'value'        => $d['value'],
                'min_order'    => $d['min_order'],
                'max_uses'     => $d['max_uses'],
                'used_count'   => 0,
                'categories'   => null,
                'expires_at'   => $now->copy()->addMonths($d['expires_in_months']),
                'is_active'    => true,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        // updateOrInsert on the unique `code` column — idempotent.
        foreach ($rows as $row) {
            DB::table('discounts')->updateOrInsert(['code' => $row['code']], $row);
        }

        $this->command->info('✅ DiscountSeeder: ' . count($rows) . ' stable discount codes.');
    }
}