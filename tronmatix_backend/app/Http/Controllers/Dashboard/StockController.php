<?php

// app/Http/Controllers/Dashboard/StockController.php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\ReceiveStockRequest;
use App\Http\Requests\ReportDamagedRequest;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function __construct(private readonly StockService $stock) {}

    /**
     * GET /dashboard/stock
     * Main stock management page — product list with current stock, plus quick
     * access to the receive/adjust/damaged forms and history links.
     */
    public function index()
    {
        $threshold = (int) \App\Models\AdminSetting::int('notif_low_stock_threshold', 5);

        $products = Product::query()
            ->orderByRaw('CASE WHEN current_stock <= ? THEN 0 ELSE 1 END', [$threshold])
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.stock.index', compact('products', 'threshold'));
    }

    /**
     * POST /dashboard/stock/receive
     */
    public function receive(ReceiveStockRequest $request)
    {
        $product = Product::findOrFail($request->product_id);
        $userId  = $this->userId();

        $this->stock->receiveStock(
            $product,
            $request->quantity,
            $request->unit_cost,
            $request->input('note'),
            $userId,
        );

        return back()->with('success', "Received {$request->quantity} units of \"{$product->name}\".");
    }

    /**
     * POST /dashboard/stock/adjust
     */
    public function adjust(AdjustStockRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        $movement = $this->stock->adjust(
            $product,
            $request->counted_quantity,
            $request->input('note'),
            $this->userId(),
        );

        if ($movement === null) {
            return back()->with('success', "No change — \"{$product->name}\" already at {$request->counted_quantity} units.");
        }

        return back()->with('success', "Stock for \"{$product->name}\" adjusted to {$request->counted_quantity} units.");
    }

    /**
     * POST /dashboard/stock/damaged
     */
    public function damaged(ReportDamagedRequest $request)
    {
        $product = Product::findOrFail($request->product_id);

        try {
            $this->stock->reportDamaged(
                $product,
                $request->quantity,
                $request->input('note'),
                $this->userId(),
            );
        } catch (InsufficientStockException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return back()->with('success', "{$request->quantity} damaged/lost unit(s) of \"{$product->name}\" recorded.");
    }

    /**
     * GET /dashboard/stock/{product}/history
     * Per-product movement timeline, newest first, paginated.
     */
    public function history(Product $product)
    {
        $movements = $product->stockMovements()
            ->with('createdBy')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('dashboard.stock.history', compact('product', 'movements'));
    }

    private function userId(): ?int
    {
        return Auth::guard('admin')->user()?->id
            ?? Auth::guard('staff')->user()?->id
            ?? null;
    }
}
