<?php

// app/Console/Commands/BackfillProductSkus.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Services\SkuGenerator;

class BackfillProductSkus extends Command
{
    protected $signature = 'skus:backfill
                            {--dry-run : Show what SKUs would be assigned without writing}';
    protected $description = 'Assign SKUs to products that do not have one yet';

    public function handle(): void
    {
        $dryRun = $this->option('dry-run');

        $products = Product::query()
            ->where(function ($q) {
                $q->whereNull('sku')->orWhere('sku', '');
            })
            ->orderBy('created_at') // earlier products get earlier assignments
            ->get();

        $this->info("Found {$products->count()} product(s) without a SKU.");
        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be written.');
        }
        $this->line('');

        $rows  = [];
        $skus  = [];

        foreach ($products as $product) {
            $sku = SkuGenerator::generate($product->category);

            // Guard against duplicates (should be impossible thanks to the
            // generator, but keep the unique index as the final backstop).
            $i = 1;
            while (isset($skus[$sku]) && $i < 50) {
                $sku = SkuGenerator::generate($product->category);
                $i++;
            }

            $skus[$sku] = true;

            $rows[] = [
                $product->id,
                $product->created_at?->format('Y-m-d'),
                $product->category,
                SkuGenerator::prefix($product->category),
                $sku,
                mb_strimwidth($product->name, 0, 40, '…'),
            ];

            if (! $dryRun) {
                $product->sku = $sku;
                $product->saveQuietly();
            }
        }

        $this->table(['ID', 'Created', 'Category', 'Prefix', 'SKU', 'Name'], $rows);

        $this->line('');
        $this->info('═══════════════════════════════════════');
        $this->info('  SKU BACKFILL SUMMARY');
        $this->info('═══════════════════════════════════════');
        $this->info("  Products assigned : {$products->count()}");
        $this->info("  Duplicates skipped: " . (count($skus) !== $products->count() ? $products->count() - count($skus) : 0));
        $this->info('═══════════════════════════════════════');

        if ($dryRun) {
            $this->line('');
            $this->warn('DRY RUN — no products were modified.');
            $this->line('Run without --dry-run to apply changes.');
        }
    }
}
