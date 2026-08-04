<?php

// database/seeders/ProductOrderNowSeeder.php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Sets stock_status = 'Available Order Now' for every product whose price is a
 * symbol-only "ask price" (e.g. "$", "$$", "$$$", "$$$$"). These are the
 * products that display "$$$" on the storefront (see isSymbolPrice: price is
 * empty, 0, or matches /^\$+$/).
 *
 * Idempotent — safe to run repeatedly.
 *
 * Usage:  php artisan db:seed --class=ProductOrderNowSeeder
 */
class ProductOrderNowSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::query()
            ->whereNotNull('price')
            ->where('price', '!=', '')
            ->whereRaw("price !~ '[0-9]'")   // no digits → symbol price ($, $$, $$$, …)
            ->get();

        $updated = 0;

        foreach ($products as $product) {
            $product->stock_status = 'Available Order Now';
            $product->save();
            $updated++;
        }

        $total = $products->count();

        $this->command->info("✅ ProductOrderNowSeeder: {$updated} symbol-price products updated.");
        $this->command->info("   {$total} total products with a symbol price.");
    }
}
