<?php

// database/seeders/ProductSeeder.php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Seeds the real Tronmatix product catalog from the JSON export.
 *
 * Source: database/data/products_catalog.json (1099 products, extracted from
 * the production pg_dump backup tronmatix_db_2026-08-10_204647.sql).
 *
 * ⚠️ NON-DESTRUCTIVE BY DESIGN — this seeder NEVER truncates the products
 * table and NEVER deletes rows. The catalog is upserted keyed on `slug`, so:
 *   - products already in the DB keep their identity & get refreshed
 *   - new/unseen entries from the JSON are inserted
 *   - anything in the DB that is NOT in the JSON is left untouched
 * This protects the live catalog: re-running `db:seed` cannot wipe real data.
 *
 * NOTE: the export has NO `sku` column, so slug is the identity key (matching
 * the original ProductImportSeeder pattern). SKUs are assigned separately by
 * the `skus:backfill` console command / the Product model's auto-generator.
 *
 * The JSON uses `stock` for inventory (mapped to the current_stock column via
 * the Product model's stock mutator). Images/specs arrays are copied as-is.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/products_catalog.json');

        if (! file_exists($path)) {
            $this->command->error("ProductSeeder: {$path} not found.");
            return;
        }

        $catalog = json_decode(file_get_contents($path), true);
        if (! is_array($catalog) || $catalog === []) {
            $this->command->error('ProductSeeder: catalog JSON is empty or invalid.');
            return;
        }

        $this->command->info("📦 ProductSeeder: loading " . count($catalog) . " products from JSON…");

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($catalog as $p) {
            $slug = $p['slug'] ?? null;

            // Slug is the identity key — products without one can't be upserted safely.
            if (! $slug) {
                $skipped++;
                continue;
            }

            $data = [
                'name'              => $p['name'] ?? null,
                'slug'              => $slug,
                'caption'           => $p['caption'] ?? null,
                'description'       => $p['description'] ?? null,
                'price'             => (string) ($p['price'] ?? ''),
                'category'          => $p['category'] ?? null,
                'brand'             => $p['brand'] ?? null,
                'brand_pc_part'     => $p['brand_pc_part'] ?? null,
                'warranty'          => $p['warranty'] ?? null,
                'image'             => $p['image'] ?? null,
                'image_disk'        => null,
                'images'            => $p['images'] ?? ($p['image'] ? [$p['image']] : null),
                'specs'             => $p['specs'] ?? [],
                'specs_title'       => $p['specs_title'] ?? null,
                'stock'             => $p['stock'] ?? 0,
                'stock_status'      => $p['stock_status'] ?? null,
                'stock_details'     => $p['stock_details'] ?? null,
                'rating'            => (float) ($p['rating'] ?? 0),
                'is_featured'       => (bool) ($p['is_featured'] ?? false),
                'is_hot'            => (bool) ($p['is_hot'] ?? false),
            ];

            if ($exists = Product::where('slug', $slug)->first()) {
                $exists->update($data);
                $updated++;
            } else {
                Product::create($data);
                $created++;
            }
        }

        // Reset the id sequence so product ids run sequentially from the current
        // max id. Idempotent: setval to MAX(id) is safe to run on every deploy.
        // Postgres-only (Render uses Postgres); SQLite has no separate sequence.
        $maxId = Product::max('id');
        if ($maxId > 0 && \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement(
                "SELECT setval(pg_get_serial_sequence('products','id'), (SELECT MAX(id) FROM products))"
            );
        }

        $this->command->info("✅ ProductSeeder: {$created} created, {$updated} updated, {$skipped} skipped (missing slug).");
        $this->command->info("   products table now has " . Product::count() . " rows.");
    }
}