<?php

// app/Console/Commands/BackfillStockLedger.php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Console\Command;

/**
 * Backfills the stock ledger with an opening-balance movement for every product
 * that has current_stock but no movement history yet.
 *
 * The `current_stock` column already holds each product's real stock (renamed
 * from the legacy `stock` column), so no stock values are invented. This command
 * only gives the ledger a starting point — one type='in' movement per product,
 * so the history page isn't empty and the running balance is traceable.
 *
 * `cost_price` is intentionally left null: there is no cost data in the system,
 * and the dashboard's inventory-value widget already treats null cost as 0 and
 * surfaces a "N products missing a cost price" note rather than a made-up number.
 *
 * Idempotent: products that already have an opening-balance movement are skipped.
 */
class BackfillStockLedger extends Command
{
    protected $signature = 'stock:backfill-ledger
                            {--dry-run : Show what would be inserted without writing}';

    protected $description = 'Create an opening-balance stock movement for each product without one';

    public function handle(): void
    {
        $dryRun = $this->option('dry-run');

        $products = Product::query()
            ->whereDoesntHave('stockMovements')
            ->get();

        $this->info("Found {$products->count()} product(s) without stock movement history.");
        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be written.');
        }
        $this->line('');

        $rows  = [];
        $count = 0;

        foreach ($products as $product) {
            if (! $product->current_stock || $product->current_stock <= 0) {
                // Skip zero-stock products — nothing to open the ledger with.
                continue;
            }

            $rows[] = [
                $product->id,
                $product->current_stock,
                mb_strimwidth($product->name, 0, 40, '…'),
            ];

            if (! $dryRun) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type'       => StockMovement::TYPE_IN,
                    'quantity'   => $product->current_stock,
                    'unit_cost'  => null, // no cost data — see docblock
                    'note'       => 'Opening balance — stock system migration',
                ]);
            }

            $count++;
        }

        $this->table(['ID', 'Opening Qty', 'Name'], $rows);

        $this->line('');
        $this->info('═══════════════════════════════════════');
        $this->info('  STOCK LEDGER BACKFILL SUMMARY');
        $this->info('═══════════════════════════════════════');
        $this->info("  Opening movements : {$count}");
        $this->info('  cost_price        : left null (no cost data)');
        $this->info('═══════════════════════════════════════');

        if ($dryRun) {
            $this->line('');
            $this->warn('DRY RUN — no movements were written.');
            $this->line('Run without --dry-run to apply.');
        }
    }
}
