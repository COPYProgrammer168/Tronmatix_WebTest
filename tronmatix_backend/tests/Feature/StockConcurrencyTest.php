<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Database-level safety-net tests for the stock system.
 *
 * NOTE: a true process-level concurrency test (two overlapping sell() calls on
 * separate connections) is intentionally NOT included here. This environment
 * has no `pcntl`/`parallel`/`pthreads` extension, and a fake concurrency test
 * that runs sequentially would pass whether or not the row lock exists — giving
 * false confidence. The row-lock correctness is instead verified by:
 *   1. the lock+transaction pattern in StockService (each method re-fetches
 *      Product::lockForUpdate()->find(id) under the lock), and
 *   2. the database check constraint tested below, which is the last-resort
 *      guarantee that stock can never go negative.
 * To run a genuine concurrency test, add `pcntl` or `parallel` to PHP and
 * launch two separate processes against the Postgres test DB.
 */
class StockConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_check_constraint_rejects_negative_stock(): void
    {
        // Use a SEPARATE raw PDO connection AND create the product on that same
        // connection. RefreshDatabase wraps the test in an uncommitted transaction,
        // so a raw connection couldn't see a row created via the app — by creating
        // the row on the raw connection too, the constraint is tested against a
        // visible row and the failed UPDATE aborts only the raw connection's
        // transaction, not the test's RefreshDatabase transaction.
        $raw = new \PDO(
            'pgsql:host=127.0.0.1;port=5433;dbname=tronmatix_test',
            'postgres',
            '12345',
        );
        $raw->beginTransaction();
        $raw->exec("INSERT INTO products (name, category, price, current_stock, created_at, updated_at) VALUES ('Constraint Test', 'CPU', '10.00', 1, NOW(), NOW())");
        $productId = (int) $raw->lastInsertId();

        $constraintHit = false;
        try {
            $raw->exec("UPDATE products SET current_stock = -1 WHERE id = {$productId}");
        } catch (\PDOException $e) {
            $constraintHit = true;
            $this->assertStringContainsString('stock_non_negative', $e->getMessage());
        }
        $this->assertTrue($constraintHit, 'Expected the stock_non_negative constraint to reject a negative update');

        // The failed statement aborted the raw connection's transaction — roll it
        // back so we don't leave it dangling.
        if ($raw->inTransaction()) {
            $raw->rollBack();
        }
    }
}
