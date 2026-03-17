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

        // Category lists match exactly what's in discounts.blade.php catGroups
        $pcBuild = ['PC BUILD UNDER 1K', 'PC BUILD UNDER 2K', 'PC BUILD UNDER 3K', 'PC BUILD UNDER 4K', 'PC BUILD UNDER 5K', 'PC BUILD 5K UP'];
        $monitors = ['MONITOR 25INCH', 'MONITOR 27INCH', 'MONITOR 32INCH', 'MONITOR 34INCH', 'MONITOR 39INCH', 'MONITOR 42INCH', 'MONITOR 48INCH', 'MONITOR 49INCH'];
        $pcParts = ['CPU', 'RAM', 'MAINBOARD', 'COOLING', 'M2', 'VGA', 'CASE', 'POWER SUPPLY', 'FAN'];
        $accessories = ['KEYBOARD', 'MOUSE', 'HEADSET', 'EARPHONE', 'MONITOR STAND', 'SPEAKER', 'MICROPHONE', 'WEBCAM', 'MOUSEPAD', 'LIGHTBAR', 'ROUTER'];
        $chairs = ['DX RACER', 'SECRETLAB', 'RAZER', 'CONSAIR', 'FANTECH', 'COOLER MASTER', 'TTR RACING'];

        $rows = [

            // ── Sitewide / welcome codes ──────────────────────────────────────
            [
                'code' => 'TRONMATIX10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order' => 0,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => null,                          // all categories
                'expires_at' => null,
                'is_active' => true,
            ],
            [
                'code' => 'WELCOME5',
                'type' => 'fixed',
                'value' => 5.00,
                'min_order' => 50.00,
                'max_uses' => 200,
                'used_count' => 0,
                'categories' => null,
                'expires_at' => $now->copy()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'FLASH20',
                'type' => 'percentage',
                'value' => 20.00,
                'min_order' => 200.00,
                'max_uses' => 50,
                'used_count' => 0,
                'categories' => null,
                'expires_at' => $now->copy()->addDays(7),
                'is_active' => true,
            ],
            [
                'code' => 'NEWUSER15',
                'type' => 'percentage',
                'value' => 15.00,
                'min_order' => 0,
                'max_uses' => 1,                              // one-time use only
                'used_count' => 0,
                'categories' => null,
                'expires_at' => null,
                'is_active' => true,
            ],

            // ── CPU / Processor ───────────────────────────────────────────────
            [
                'code' => 'CPU15',
                'type' => 'percentage',
                'value' => 15.00,
                'min_order' => 100.00,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode(['CPU']),
                'expires_at' => null,
                'is_active' => true,
            ],
            [
                'code' => 'AMD25OFF',
                'type' => 'fixed',
                'value' => 25.00,
                'min_order' => 250.00,
                'max_uses' => 30,
                'used_count' => 0,
                'categories' => json_encode(['CPU']),
                'expires_at' => $now->copy()->addMonths(2),
                'is_active' => true,
            ],

            // ── VGA / GPU ─────────────────────────────────────────────────────
            [
                'code' => 'VGA10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order' => 300.00,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode(['VGA']),
                'expires_at' => null,
                'is_active' => true,
            ],
            [
                'code' => 'RTX30OFF',
                'type' => 'fixed',
                'value' => 30.00,
                'min_order' => 400.00,
                'max_uses' => 20,
                'used_count' => 0,
                'categories' => json_encode(['VGA']),
                'expires_at' => $now->copy()->addMonths(1),
                'is_active' => true,
            ],

            // ── RAM ───────────────────────────────────────────────────────────
            [
                'code' => 'RAM10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order' => 50.00,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode(['RAM']),
                'expires_at' => null,
                'is_active' => true,
            ],

            // ── Storage / M.2 ─────────────────────────────────────────────────
            [
                'code' => 'SSD15',
                'type' => 'percentage',
                'value' => 15.00,
                'min_order' => 0,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode(['M2']),
                'expires_at' => null,
                'is_active' => true,
            ],

            // ── Cooling / Fan ─────────────────────────────────────────────────
            [
                'code' => 'COOL10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order' => 0,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode(['COOLING', 'FAN']),
                'expires_at' => null,
                'is_active' => true,
            ],

            // ── Full PC Parts bundle ──────────────────────────────────────────
            [
                'code' => 'PARTS12',
                'type' => 'percentage',
                'value' => 12.00,
                'min_order' => 150.00,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode($pcParts),
                'expires_at' => null,
                'is_active' => true,
            ],

            // ── PC Build combos ───────────────────────────────────────────────
            [
                'code' => 'BUILD50',
                'type' => 'fixed',
                'value' => 50.00,
                'min_order' => 800.00,
                'max_uses' => 15,
                'used_count' => 0,
                'categories' => json_encode($pcBuild),
                'expires_at' => $now->copy()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'SETUP8',
                'type' => 'percentage',
                'value' => 8.00,
                'min_order' => 500.00,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode($pcBuild),
                'expires_at' => null,
                'is_active' => true,
            ],

            // ── Monitor ───────────────────────────────────────────────────────
            [
                'code' => 'MONITOR8',
                'type' => 'percentage',
                'value' => 8.00,
                'min_order' => 150.00,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode($monitors),
                'expires_at' => null,
                'is_active' => true,
            ],
            [
                'code' => 'SCREEN20',
                'type' => 'fixed',
                'value' => 20.00,
                'min_order' => 300.00,
                'max_uses' => 25,
                'used_count' => 0,
                'categories' => json_encode($monitors),
                'expires_at' => $now->copy()->addMonths(2),
                'is_active' => true,
            ],

            // ── Accessories ───────────────────────────────────────────────────
            [
                'code' => 'ACCESS12',
                'type' => 'percentage',
                'value' => 12.00,
                'min_order' => 30.00,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode($accessories),
                'expires_at' => null,
                'is_active' => true,
            ],
            [
                'code' => 'GEAR5OFF',
                'type' => 'fixed',
                'value' => 5.00,
                'min_order' => 50.00,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode(['KEYBOARD', 'MOUSE', 'MOUSEPAD', 'HEADSET']),
                'expires_at' => null,
                'is_active' => true,
            ],

            // ── Chair / Desk ──────────────────────────────────────────────────
            [
                'code' => 'CHAIR10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order' => 100.00,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode($chairs),
                'expires_at' => null,
                'is_active' => true,
            ],

            // ── Hot items ─────────────────────────────────────────────────────
            [
                'code' => 'BESTDEAL',
                'type' => 'percentage',
                'value' => 5.00,
                'min_order' => 0,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => json_encode(['BEST PRICE', 'BEST SET']),
                'expires_at' => null,
                'is_active' => true,
            ],

            // ── Test / dev fixtures ───────────────────────────────────────────
            [
                'code' => 'EXPIRED25',
                'type' => 'percentage',
                'value' => 25.00,
                'min_order' => 0,
                'max_uses' => null,
                'used_count' => 5,
                'categories' => null,
                'expires_at' => $now->copy()->subDays(10),     // already expired
                'is_active' => true,
            ],
            [
                'code' => 'DISABLED',
                'type' => 'fixed',
                'value' => 10.00,
                'min_order' => 0,
                'max_uses' => null,
                'used_count' => 0,
                'categories' => null,
                'expires_at' => null,
                'is_active' => false,                          // admin disabled
            ],
        ];

        // Attach timestamps and cast categories to JSON where needed
        $insert = array_map(function ($row) use ($now) {
            if (isset($row['categories']) && is_array($row['categories'])) {
                $row['categories'] = json_encode($row['categories']);
            }
            $row['created_at'] = $now;
            $row['updated_at'] = $now;

            return $row;
        }, $rows);

        DB::table('discounts')->insert($insert);

        $this->command->info('✅  DiscountSeeder: '.count($insert).' codes inserted.');
    }
}
