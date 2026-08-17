<?php

// app/Http/Controllers/Dashboard/StockController.php

namespace App\Http\Controllers\Dashboard;

use App\Exceptions\InsufficientStockException;
use App\Exports\StockExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\ReceiveStockRequest;
use App\Http\Requests\ReportDamagedRequest;
use App\Http\Requests\ResetStockRequest;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

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

        // Distinct categories, used by the "Reset / Randomize" scope select.
        $categories = Product::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('dashboard.stock.index', compact('products', 'threshold', 'categories'));
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
     * POST /dashboard/stock/reset
     * Bulk-randomize stock levels ("more or less") across all products or a
     * single category, so the shop shows a realistic demo spread. Every change
     * is recorded as an 'adjustment' movement by StockService::resetRandom().
     */
    public function resetRandom(ResetStockRequest $request)
    {
        $scope = $request->input('scope');

        $productIds = $scope === 'category'
            ? Product::where('category', $request->input('category'))->pluck('id')->all()
            : Product::pluck('id')->all();

        if (empty($productIds)) {
            return back()->with('error', 'No products matched that scope — nothing to reset.');
        }

        $changed = $this->stock->resetRandom(
            $productIds,
            $request->input('note'),
            $this->userId(),
        );

        return back()->with(
            'success',
            "Stock randomized for {$changed} of " . count($productIds) . " products (more or less).",
        );
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

    /**
     * GET /dashboard/stock/report
     * Stock movements report with optional filters.
     */
    public function report(Request $request)
    {
        $from    = $request->input('from', '');
        $to      = $request->input('to', '');
        $type    = $request->input('type', '');
        $product = $request->input('product_id', '');

        $query = StockMovement::query()
            ->with(['createdBy:id,name', 'product:id,name'])
            ->orderByDesc('created_at');

        if ($from) {
            $query->whereDate('created_at', '>=', \Carbon\Carbon::parse($from)->startOfDay());
        }
        if ($to) {
            $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($to)->endOfDay());
        }
        if ($type) {
            $query->where('type', $type);
        }
        if ($product) {
            $query->where('product_id', $product);
        }

        $movements = $query->paginate(50)->withQueryString();
        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('dashboard.stock.report', compact('movements', 'products', 'from', 'to', 'type', 'product'));
    }

    /**
     * GET /dashboard/stock/export
     * Export stock movements to Excel.
     */
    public function export(Request $request)
    {
        $from = $request->input('from', '');
        $to   = $request->input('to', '');
        $type = $request->input('type', '');

        $filename = 'stock-movements-' . ($from ?: 'all') . '-to-' . ($to ?: 'now') . '.xlsx';

        return Excel::download(new StockExport($from, $to, $type ?: null), $filename);
    }

    private function userId(): ?int
    {
        return Auth::guard('admin')->user()?->id
            ?? Auth::guard('staff')->user()?->id
            ?? null;
    }
}
