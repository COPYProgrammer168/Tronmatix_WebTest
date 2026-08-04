<?php

// database/seeders/ProductWarrantySeeder.php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Backfills a random 1–3 year warranty for every product that has none.
 *
 * The OrderController parses the warranty string ("2 years" / "12 months") to
 * derive each order item's warranty_start/end, so the format here is chosen to
 * match: "1 year", "2 years", "3 years".
 *
 * Idempotent: products that already have a warranty are left untouched, so it
 * is safe to run repeatedly.
 *
 * Usage:  php artisan db:seed --class=ProductWarrantySeeder
 */
class ProductWarrantySeeder extends Seeder
{
    public function run(): void
    {
        $years = [1, 2, 3];

        $products = Product::whereNull('warranty')
            ->orWhere('warranty', '')
            ->get();

        $updated = 0;

        foreach ($products as $product) {
            $yearsCount = $years[array_rand($years)];

            $product->warranty = $yearsCount === 1 ? '1 year' : "{$yearsCount} years";
            $product->save();

            $updated++;
        }

        $total = Product::count();
        $withWarranty = Product::whereNotNull('warranty')
            ->where('warranty', '!=', '')
            ->count();

        $this->command->info("✅ ProductWarrantySeeder: {$updated} products updated.");
        $this->command->info("   {$withWarranty}/{$total} products now have a warranty.");
    }
}
