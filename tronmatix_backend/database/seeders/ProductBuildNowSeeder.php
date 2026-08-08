<?php

// database/seeders/ProductBuildNowSeeder.php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Sets stock_status = 'Available Build Now' for every PC BUILD product so the
 * storefront shows that exact status on each PC build card / detail page.
 *
 * PC BUILD products are stored under category strings that start with
 * "PC BUILD" (e.g. "PC BUILD UNDER 2K", "PC BUILD UNDER 3K", "PC BUILD 5K UP"),
 * so we match on a case-insensitive prefix.
 *
 * Idempotent: re-running is safe. Only products whose status is currently empty
 * or the generic "Available InStock Now" are touched, so admin-set custom
 * statuses (e.g. "Sold Out") are preserved.
 *
 * Usage:  php artisan db:seed --class=ProductBuildNowSeeder
 */
class ProductBuildNowSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('category', 'ilike', 'PC BUILD%')
            ->where(function ($q) {
                $q->whereNull('stock_status')
                  ->orWhere('stock_status', '')
                  ->orWhere('stock_status', 'Available InStock Now');
            })
            ->get();

        $updated = 0;

        foreach ($products as $product) {
            $product->stock_status = 'Available Build Now';
            $product->save();
            $updated++;
        }

        $total = Product::where('category', 'ilike', 'PC BUILD%')->count();

        $this->command->info("✅ ProductBuildNowSeeder: {$updated} PC BUILD products updated.");
        $this->command->info("   {$total} total PC BUILD products.");
    }
}
