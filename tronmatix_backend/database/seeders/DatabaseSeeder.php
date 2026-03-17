<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // FIX: original used PostgreSQL syntax (SET session_replication_role,
        // TRUNCATE … RESTART IDENTITY CASCADE) — this project uses MySQL.
        // MySQL equivalent is FOREIGN_KEY_CHECKS=0 + plain truncate().
        Schema::disableForeignKeyConstraints();

        DB::table('chat_messages')->truncate();
        DB::table('chat_sessions')->truncate();
        DB::table('payments')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        DB::table('discounts')->truncate();          // FIX: was missing entirely
        DB::table('delivery_schedules')->truncate(); // FIX: was missing entirely
        DB::table('user_locations')->truncate();
        DB::table('products')->truncate();
        DB::table('banners')->truncate();
        DB::table('admins')->truncate();
        DB::table('users')->truncate();

        Schema::enableForeignKeyConstraints();

        // ── Run seeders in dependency order ───────────────────────────────────
        $this->call([
            AdminSeeder::class,
            BannerSeeder::class,
            ProductSeeder::class,           // products must exist before discounts
            UserSeeder::class,
            UserLocationSeeder::class,      // after UserSeeder
            OrderSeeder::class,             // after UserLocationSeeder
            DiscountSeeder::class,          // FIX: was missing — seeds all discount codes
            DeliveryScheduleSeeder::class,  // FIX: was missing — seeds Mon–Sat time slots
        ]);
    }
}
