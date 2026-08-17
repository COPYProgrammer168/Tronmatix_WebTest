<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master seeder for the new demo system.
 *
 * Every `db:seed` run performs a full reset of the DEMO tables (with FK
 * constraints disabled) before re-seeding, so the counts below stay pinned
 * every time:
 *
 *   Users             100  (all VIP)
 *   Staff              10  (Khmer names)
 *   User locations    ~100–300  (real Cambodia addresses + coords)
 *   Discounts           5  (stable codes)
 *   Products          NOT TOUCHED — the real catalog (1099 products) is
 *                     preserved. StockSeeder only seeds demo products when
 *                     the products table is completely empty.
 *   Orders            150  (all in the CURRENT month)
 *   Order items       ~150–450  (1–3 per order)
 *   Payments          ~150  (one per order)
 *
 * Dependency order matters: users → locations → discounts → products →
 * orders(+items) → payments.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->resetDatabase();

        $this->call([
            UserSeeder::class,          // 100 VIP users
            StaffSeeder::class,         // 10 staff (Khmer)
            UserLocationSeeder::class,  // real Cambodia delivery addresses
            DiscountSeeder::class,      // stable discount codes
            // StockSeeder::class,       
            OrderSeeder::class,         // 150 current-month orders + items
            PaymentSeeder::class,       // payment rows for every order
        ]);
    }

    private function resetDatabase(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('payments')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        // DB::table('stock_movements')->truncate();
        DB::table('discounts')->truncate();
        DB::table('user_locations')->truncate();
        DB::table('staff')->truncate();
        DB::table('users')->truncate();
        // NOTE: products intentionally NOT truncated — this is the real catalog
        // (1099 products). Only StockSeeder refreshes demo rows when the table
        // is empty; it never wipes the live catalog.

        Schema::enableForeignKeyConstraints();

        $this->command->info('🧹 Database reset — all demo tables truncated.');
    }
}