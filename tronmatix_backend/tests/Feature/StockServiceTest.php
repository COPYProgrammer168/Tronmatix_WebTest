<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 0): Product
    {
        return Product::create([
            'name'          => 'Test Product ' . uniqid(),
            'category'      => 'CPU',
            'price'         => '100.00',
            'current_stock' => $stock,
        ]);
    }

    public function test_receive_stock_increases_stock_and_records_movement(): void
    {
        $product = $this->makeProduct(0);
        $svc = app(StockService::class);

        $movement = $svc->receiveStock($product, 10, 5.00, 'opening balance');

        $this->assertSame(10, $product->fresh()->current_stock);
        $this->assertSame('in', $movement->type);
        $this->assertSame(10, $movement->quantity);
        $this->assertSame('5.00', (string) $movement->unit_cost);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'type' => 'in']);
    }

    public function test_sell_decreases_stock(): void
    {
        $product = $this->makeProduct(10);
        $svc = app(StockService::class);

        $movement = $svc->sell($product, 4);

        $this->assertSame(6, $product->fresh()->current_stock);
        $this->assertSame('out', $movement->type);
        $this->assertSame(-4, $movement->quantity);
    }

    public function test_sell_throws_when_insufficient(): void
    {
        $product = $this->makeProduct(3);
        $svc = app(StockService::class);

        $this->expectException(InsufficientStockException::class);
        $svc->sell($product, 5);
    }

    public function test_adjust_logs_difference_in_both_directions(): void
    {
        $svc = app(StockService::class);

        $product = $this->makeProduct(10);
        $svc->adjust($product, 15, 'counted higher');
        $this->assertSame(15, $product->fresh()->current_stock);

        $product = $this->makeProduct(10);
        $svc->adjust($product, 4, 'counted lower');
        $this->assertSame(4, $product->fresh()->current_stock);
    }

    public function test_adjust_with_no_difference_returns_null_and_creates_no_row(): void
    {
        $product = $this->makeProduct(10);
        $svc = app(StockService::class);

        $movement = $svc->adjust($product, 10, 'no change');

        $this->assertNull($movement);
        $this->assertSame(10, $product->fresh()->current_stock);
        $this->assertDatabaseMissing('stock_movements', ['product_id' => $product->id, 'type' => 'adjustment']);
    }

    public function test_report_damaged_decreases_stock_and_requires_note(): void
    {
        $product = $this->makeProduct(10);
        $svc = app(StockService::class);

        $movement = $svc->reportDamaged($product, 3, 'broken in shipping');

        $this->assertSame(7, $product->fresh()->current_stock);
        $this->assertSame('damaged', $movement->type);
        $this->assertSame(-3, $movement->quantity);
    }

    public function test_reverse_sell_restores_stock(): void
    {
        $product = $this->makeProduct(10);
        $svc = app(StockService::class);

        $sale = $svc->sell($product, 4);
        $this->assertSame(6, $product->fresh()->current_stock);

        $reversal = $svc->reverse($sale);

        $this->assertSame(10, $product->fresh()->current_stock);
        $this->assertSame('reversal', $reversal->type);
        $this->assertSame(4, $reversal->quantity);
        $this->assertSame($sale->id, $reversal->reversed_movement_id);
        // sale's unit_cost is null → reversal copies null
        $this->assertNull($reversal->unit_cost);
    }

    public function test_reversing_same_movement_twice_throws(): void
    {
        $product = $this->makeProduct(10);
        $svc = app(StockService::class);

        $sale = $svc->sell($product, 4);
        $svc->reverse($sale);

        $this->expectException(\RuntimeException::class);
        $svc->reverse($sale);
    }

    public function test_reversing_receive_after_stock_sold_throws_insufficient(): void
    {
        $product = $this->makeProduct(0);
        $svc = app(StockService::class);

        // receive 10, sell 8 → 2 left
        $receive = $svc->receiveStock($product, 10, 5.00);
        $svc->sell($product, 8);
        $this->assertSame(2, $product->fresh()->current_stock);

        // reversing the 10-unit receive would try to remove 10 from a product
        // with only 2 left → must throw, never go negative.
        $this->expectException(InsufficientStockException::class);
        $svc->reverse($receive);
    }
}
