<?php

// app/Console/Commands/BackfillBrandId.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;

class BackfillBrandId extends Command
{
    protected $signature = 'categories:backfill-brands
                            {--dry-run : Show what would be changed without writing}
                            {--review-only : Only show products that need manual review}';
    protected $description = 'Match existing products to the category tree and assign brand_id where unambiguous';

    public function handle(): void
    {
        $dryRun = $this->option('dry-run');
        $reviewOnly = $this->option('review-only');

        $products = Product::all();
        $tree = Category::with(['mainCategories.subCategories.brands'])->get();

        $this->info("Loaded {$products->count()} products and {$tree->count()} top-level categories.");
        if ($dryRun) {
            $this->warn("DRY RUN — no changes will be written.");
        }
        if ($reviewOnly) {
            $this->warn("REVIEW ONLY — only showing products that need manual review.");
        }
        $this->line('');

        // Build lookup maps
        $brandMap = [];     // [normalized_name => [brand_id, path_string]]
        $nameToBrands = []; // [normalized_name => [brand, ...]] for ambiguity detection

        foreach ($tree as $category) {
            foreach ($category->mainCategories as $mc) {
                foreach ($mc->subCategories as $sc) {
                    foreach ($sc->brands as $brand) {
                        $norm = strtolower($brand->name);
                        $path = $category->name . ' > ' . $mc->name . ' > ' . $sc->name . ' > ' . $brand->name;

                        $brandMap[$norm] = ['id' => $brand->id, 'path' => $path];

                        if (!isset($nameToBrands[$norm])) {
                            $nameToBrands[$norm] = [];
                        }
                        $nameToBrands[$norm][] = ['id' => $brand->id, 'name' => $brand->name, 'path' => $path];
                    }
                }
            }
        }

        $this->info("Indexed " . count($brandMap) . " unique brand name entries.");
        $this->line('');

        $matched = 0;
        $unmatched = 0;
        $ambiguous = 0;
        $reviewList = [];

        foreach ($products as $product) {
            $catText = strtolower(trim($product->category ?? ''));

            if (empty($catText)) {
                $unmatched++;
                $reviewList[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category ?? '(empty)',
                    'reason' => 'Empty category field',
                ];
                continue;
            }

            // Check for exact brand name match first
            if (isset($nameToBrands[$catText])) {
                $matches = $nameToBrands[$catText];

                if (count($matches) === 1) {
                    $brandId = $matches[0]['id'];

                    if (!$dryRun && !$reviewOnly) {
                        $product->brand_id = $brandId;
                        $product->save();
                    }

                    $matched++;
                    continue;
                } else {
                    $ambiguous++;
                    $reviewList[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'category' => $product->category,
                        'reason' => 'Ambiguous: matches ' . count($matches) . ' brands: ' .
                            implode(', ', array_column($matches, 'name')),
                    ];
                    continue;
                }
            }

            // No exact match — check for substring matches
            $possibleBrands = [];
            foreach ($nameToBrands as $normName => $matches) {
                if (str_contains($catText, $normName)) {
                    $possibleBrands = array_merge($possibleBrands, $matches);
                }
            }

            $uniqueBrandIds = array_unique(array_column($possibleBrands, 'id'));

            if (count($uniqueBrandIds) === 1) {
                $brandId = $uniqueBrandIds[0];

                if (!$dryRun && !$reviewOnly) {
                    $product->brand_id = $brandId;
                    $product->save();
                }

                $matched++;
            } elseif (count($uniqueBrandIds) === 0) {
                $unmatched++;
                $reviewList[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'reason' => 'No matching brand found in category tree',
                ];
            } else {
                $ambiguous++;
                $reviewList[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'reason' => 'Ambiguous substring match: ' . count($uniqueBrandIds) . ' possible brands',
                ];
            }
        }

        // Summary
        $this->line('');
        $this->info('═══════════════════════════════════════');
        $this->info('  BACKFILL SUMMARY');
        $this->info('═══════════════════════════════════════');
        $this->info("  Auto-matched : {$matched}");
        $this->warn("  Unmatched    : {$unmatched}");
        $this->warn("  Ambiguous    : {$ambiguous}");
        $this->info("  Total        : {$products->count()}");
        $this->info('═══════════════════════════════════════');

        if (!empty($reviewList)) {
            $this->line('');
            $this->warn('Products needing manual review:');
            $this->line('');

            $headers = ['ID', 'Product Name', 'Category String', 'Reason'];
            $rows = [];

            foreach ($reviewList as $item) {
                $rows[] = [
                    $item['id'],
                    mb_strimwidth($item['name'], 0, 30, '...'),
                    mb_strimwidth($item['category'], 0, 25, '...'),
                    mb_strimwidth($item['reason'], 0, 50, '...'),
                ];
            }

            $this->table($headers, $rows);
        }

        if ($dryRun) {
            $this->line('');
            $this->warn('DRY RUN — no products were modified.');
            $this->line('Run without --dry-run to apply changes.');
        }
    }
}
