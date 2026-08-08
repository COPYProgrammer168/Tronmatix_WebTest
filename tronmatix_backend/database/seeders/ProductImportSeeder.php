<?php

// database/seeders/ProductImportSeeder.php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Imports the full product catalog (1,098 products) from a committed JSON data
 * file into the products table.
 *
 * The catalog was exported from the local snapshot recovered from the live
 * site tronmatixcomputer.com (real names, prices, specs, and images hotlinked
 * from the original site). Having it as a committed data file means the Render
 * deploy's `php artisan db:seed --force` re-syncs the catalog on every boot.
 *
 * Idempotent: upserts by `slug` (stable unique key), so re-running never
 * duplicates rows and stays safe to run on every deploy. Does NOT truncate the
 * products table — products added manually via the admin dashboard are kept.
 *
 * Usage:  php artisan db:seed --class=ProductImportSeeder
 */
class ProductImportSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/products_catalog.json');

        if (! file_exists($path)) {
            $this->command->warn("⚠️  ProductImportSeeder: {$path} not found — skipping.");
            return;
        }

        $rows = json_decode(file_get_contents($path), true);
        if (! is_array($rows) || ! $rows) {
            $this->command->warn('⚠️  ProductImportSeeder: catalog JSON is empty or invalid — skipping.');
            return;
        }

        $created  = 0;
        $updated  = 0;
        $skipped  = 0;

        foreach ($rows as $row) {
            // Upsert keyed on the stable slug. Ignore the exported `id` column.
            $slug = $row['slug'] ?? null;
            if (! $slug) {
                $skipped++;
                continue;
            }

            $data = [
                'name'          => $row['name'],
                'slug'          => $slug,
                'caption'       => $row['caption']       ?? null,
                'description'   => $row['description']   ?? null,
                'price'         => $row['price'],
                'category'      => $row['category'],
                'brand'         => $row['brand']         ?? null,
                'warranty'      => $row['warranty']      ?? null,
                'image'         => $row['image']         ?? null,
                'image_disk'    => $row['image_disk']    ?? 'url',
                'images'        => $row['images']        ?? ($row['image'] ? [$row['image']] : null),
                'specs'         => $row['specs']         ?? [],
                'specs_title'   => $row['specs_title']   ?? null,
                'stock'         => $row['stock'],
                'stock_status'  => $row['stock_status']  ?? null,
                'stock_details' => $row['stock_details'] ?? null,
                'brand_pc_part' => $row['brand_pc_part'] ?? null,
                'rating'        => $row['rating']        ?? 0,
                'is_featured'   => ! empty($row['is_featured']),
                'is_hot'        => ! empty($row['is_hot']),
            ];

            $product = Product::where('slug', $slug)->first();

            if ($product) {
                $product->fill($data)->save();
                $updated++;
            } else {
                Product::create($data);
                $created++;
            }
        }

        // ── Reset the id sequence so product ids run sequentially from the ────
        // current max id (== 1,098 on a fresh import → next new product gets
        // id 1,099). This keeps Product::create() ids contiguous on the Render
        // DB instead of jumping ahead. Idempotent: setval to MAX(id) is safe to
        // run on every deploy. Postgres-only (Render uses Postgres); SQLite has
        // no separate sequence so it's skipped there.
        $maxId = Product::max('id');
        if ($maxId > 0 && \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement(
                "SELECT setval(pg_get_serial_sequence('products','id'), (SELECT MAX(id) FROM products))"
            );
        }

        $total = Product::count();
        $this->command->info("✅ ProductImportSeeder: {$created} created, {$updated} updated, {$skipped} skipped.");
        $this->command->info("   products table now has {$total} rows (next id = " . ($maxId + 1) . ").");
    }
}