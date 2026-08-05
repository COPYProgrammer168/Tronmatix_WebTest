<?php

// database/seeders/ProductSkuSeeder.php

namespace Database\Seeders;

use App\Models\Product;
use App\Services\SkuGenerator;
use Illuminate\Database\Seeder;

/**
 * Assigns a SKU to every product that does not have one yet.
 *
 * SKU format: {CATEGORY_PREFIX}{5 random chars} — e.g. CPUD5E9W, RAMD46PQ.
 * The prefix is derived from the product's category string via SkuGenerator's
 * PREFIX_MAP (fixed abbreviations with an uppercase-first-word fallback). The
 * suffix is random and collision-guarded by the generator + the unique `sku`
 * index, so no two products can ever share a SKU.
 *
 * Idempotent: products that already have a SKU are left untouched, so it is
 * safe to run repeatedly.
 *
 * Usage:  php artisan db:seed --class=ProductSkuSeeder
 */
class ProductSkuSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::whereNull('sku')
            ->orWhere('sku', '')
            ->get();

        $updated = 0;

        foreach ($products as $product) {
            $product->sku = SkuGenerator::generate($product->category);
            $product->saveQuietly(); // bypass the saving hook; sku set directly

            $updated++;
        }

        $total = Product::count();
        $withSku = Product::whereNotNull('sku')
            ->where('sku', '!=', '')
            ->count();

        $this->command->info("✅ ProductSkuSeeder: {$updated} products assigned a SKU.");
        $this->command->info("   {$withSku}/{$total} products now have a SKU.");
    }
}
