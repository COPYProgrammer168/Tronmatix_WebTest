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
        // ── Safety guard: only truncate + re-seed on an EMPTY database. ───────
        // This lets `php artisan db:seed` run safely on Render/production without
        // wiping real users, orders, etc. on every deploy. Pass SEED_FRESH=1 to
        // force a full truncate + reseed (e.g. `SEED_FRESH=1 php artisan db:seed`).
        $alreadySeeded = \App\Models\User::count() > 0;

        if ((bool) env('SEED_FRESH', false) || ! $alreadySeeded) {
            Schema::disableForeignKeyConstraints();

            DB::table('payments')->truncate();
            DB::table('order_items')->truncate();
            DB::table('orders')->truncate();
            // DB::table('brands')->truncate();
            // DB::table('sub_categories')->truncate();
            // DB::table('main_categories')->truncate();
            // DB::table('categories')->truncate();
            DB::table('discounts')->truncate();
            // DB::table('user_locations')->truncate();
            DB::table('staff')->truncate();
            DB::table('users')->truncate();

            Schema::enableForeignKeyConstraints();

            $this->command->info('🔄 Database empty — truncating + seeding from scratch.');
        } else {
            $this->command->warn('⚠️  Database already has users — SKIPPING destructive truncate.');
            $this->command->warn('    Run `SEED_FRESH=1 php artisan db:seed` to force a clean reseed.');
        }

        // ── Run seeders in dependency order ───────────────────────────────────
        $this->call([
            // AdminSeeder::class,
            // StaffSeeder::class,
            UserSeeder::class,
            CustomerSeeder::class,
            UserLocationSeeder::class,
            DiscountSeeder::class,
            // CategorySeeder::class,
            StockDemoSeeder::class,
            // DeliveryZoneSeeder::class,
            // ProvinceSeeder::class,
            OrderSeeder::class,
            // ProductSkuSeeder::class,
            // ProductImportSeeder::class,
            // ActivityLogSeeder::class,       // login events incl. current month
            // DeliveryScheduleSeeder::class,
            // MarqueeSeeder::class,           // Telegram connect marquee messages
        ]);
    }
}
