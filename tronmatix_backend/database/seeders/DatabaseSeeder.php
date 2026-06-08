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
        Schema::disableForeignKeyConstraints();

        // DB::table('chat_messages')->truncate();
        // DB::table('chat_sessions')->truncate();
        DB::table('payments')->truncate();
        DB::table('order_items')->truncate();
        DB::table('orders')->truncate();
        // DB::table('discounts')->truncate();
        DB::table('delivery_schedules')->truncate();
        DB::table('user_locations')->truncate();
        // DB::table('products')->truncate();
        // DB::table('banners')->truncate();
        DB::table('admins')->truncate();
        DB::table('staff')->truncate();
        DB::table('users')->truncate();

        Schema::enableForeignKeyConstraints();

        // ── Run seeders in dependency order ───────────────────────────────────
        $this->call([
            AdminSeeder::class,
            StaffSeeder::class,
            UserSeeder::class,
            UserLocationSeeder::class,      // after UserSeeder
            DiscountSeeder::class,          // Populate discounts first
            OrderSeeder::class,             // then create orders
            DeliveryScheduleSeeder::class,
        ]);
    }
}