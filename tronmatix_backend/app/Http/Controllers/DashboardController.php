<?php

// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Models\Banner;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Exports\DashboardExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    // ── Dashboard index ───────────────────────────────────────────────────────
    public function index()
    {
        // ── Discount aggregates ───────────────────────────────────────────────
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth   = Carbon::now()->endOfMonth();

        $stats = [
            'total_users'    => User::count(),
            'total_products' => Product::count(),
            'total_orders'   => Order::count(),
            'total_revenue'  => Order::whereNotIn('status', ['cancelled'])->sum('total'),
            'pending_orders' => Order::whereIn('status', ['pending', 'confirmed'])->count(),
            'active_orders'  => Order::whereIn('status', ['confirmed', 'processing', 'shipped'])->count(),

            // Total discount amount saved across ALL orders (all time)
            'total_discount_used' => (float) Order::whereNotNull('discount_amount')
                ->where('discount_amount', '>', 0)
                ->sum('discount_amount'),

            // Count of currently active (non-expired) discount codes
            'active_discounts' => Discount::active()->count(),

            // Discount savings within the current calendar month
            'monthly_discount_used' => (float) Order::whereNotNull('discount_amount')
                ->where('discount_amount', '>', 0)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('discount_amount'),

            // Number of orders that used a discount code this month
            'monthly_discount_count' => Order::whereNotNull('discount_id')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count(),
        ];

        // Monthly sales — last 12 months
        $salesRows = Order::select(
            DB::raw($this->dateFormatExpr('created_at', 'Y-m').' as month_key'),
            DB::raw('SUM(total) as revenue'),
            DB::raw('COUNT(*) as orders')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()->keyBy('month_key');

        $monthlyLabels = $monthlyRevenue = $monthlyOrders = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key = $date->format('Y-m');
            $found = $salesRows->get($key);
            $monthlyLabels[] = $date->format('M Y');
            $monthlyRevenue[] = $found ? round((float) $found->revenue, 2) : 0;
            $monthlyOrders[] = $found ? (int) $found->orders : 0;
        }

        // Monthly user registrations
        $userRows = User::select(
            DB::raw($this->dateFormatExpr('created_at', 'Y-m').' as month_key'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month_key')->orderBy('month_key')
            ->get()->keyBy('month_key');

        $monthlyUserCounts = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = Carbon::now()->subMonths($i)->format('Y-m');
            $found = $userRows->get($key);
            $monthlyUserCounts[] = $found ? (int) $found->total : 0;
        }

        // Order status pie
        $orderStatus = Order::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')->get()
            ->mapWithKeys(fn ($item) => [$item->status => (int) $item->total]);

        $statusLabels = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $statusCounts = collect($statusLabels)->map(fn ($s) => $orderStatus->get($s, 0))->values()->toArray();

        // Sales by category
        $categoryRevenue = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['cancelled'])
            ->select('products.category', DB::raw('SUM(order_items.price * order_items.qty) as revenue'))
            ->groupBy('products.category')->orderByDesc('revenue')->limit(6)->get();

        $categoryLabels = $categoryRevenue->pluck('category')->toArray();
        $categoryRevData = $categoryRevenue->pluck('revenue')->map(fn ($v) => round((float) $v, 2))->toArray();

        // Daily sales — last 14 days
        $dailyRows = Order::select(
            DB::raw($this->dateFormatExpr('created_at', 'Y-m-d').' as date_key'),
            DB::raw('SUM(total) as revenue')
        )
            ->where('created_at', '>=', Carbon::now()->subDays(13)->startOfDay())
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('date_key')->orderBy('date_key')
            ->get()->keyBy('date_key');

        $dailyLabels = $dailyRevenue = [];
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $key = $date->toDateString();
            $found = $dailyRows->get($key);
            $dailyLabels[] = $date->format('d M');
            $dailyRevenue[] = $found ? round((float) $found->revenue, 2) : 0;
        }

        $top_products = Product::withCount('orderItems')->orderByDesc('order_items_count')->take(5)->get();

        // FIX [1]: use AdminSetting threshold, not hardcoded 5
        $threshold = AdminSetting::int('notif_low_stock_threshold', 5);
        $low_stock = Product::lowStock()->orderBy('stock')->take(5)->get(); // FIX [1]

        // FIX [3]: eager-load 'location'
        $recent_orders = Order::with(['user', 'items', 'location'])->latest()->take(8)->get();

        // Top discount codes used this month — for the dashboard mini-table
        $top_discount_codes = Discount::select(
                'discounts.*',
                DB::raw('COUNT(orders.id)                            AS monthly_uses'),
                DB::raw('COALESCE(SUM(orders.discount_amount), 0)   AS monthly_saved')
            )
            ->join('orders', function ($join) use ($startOfMonth, $endOfMonth) {
                $join->on('orders.discount_id', '=', 'discounts.id')
                     ->whereBetween('orders.created_at', [$startOfMonth, $endOfMonth]);
            })
            ->groupBy('discounts.id')
            ->orderByDesc('monthly_saved')
            ->limit(8)
            ->get();

        return view('dashboard.index', compact(
            'stats', 'recent_orders', 'top_products', 'low_stock',
            'monthlyLabels', 'monthlyRevenue', 'monthlyOrders', 'monthlyUserCounts',
            'statusLabels', 'statusCounts', 'categoryLabels', 'categoryRevData',
            'dailyLabels', 'dailyRevenue', 'top_discount_codes'
        ));
    }

    // ── Products list ─────────────────────────────────────────────────────────
    public function products()
    {
        $query = Product::query();

        if (request('search')) {
            $term = '%'.request('search').'%';
            $query->where(fn ($q) => $q->where('name', 'LIKE', $term)
                ->orWhere('brand', 'LIKE', $term)
                ->orWhere('category', 'LIKE', $term));
        }
        if (request('category')) {
            $query->whereRaw('LOWER(category) = ?', [strtolower(request('category'))]);
        }

        // FIX [2]: low/in stock filters use AdminSetting threshold
        $threshold = AdminSetting::int('notif_low_stock_threshold', 5);
        if (request('stock') === 'out') {
            $query->where('stock', '<=', 0);
        } elseif (request('stock') === 'low') {
            $query->where('stock', '>', 0)->where('stock', '<=', $threshold); // FIX [2]
        } elseif (request('stock') === 'in') {
            $query->where(fn ($q) => $q->whereNull('stock')->orWhere('stock', '>', 0));
        }

        if (request('filter') === 'hot') {
            $query->where('is_hot', true);
        } elseif (request('filter') === 'featured') {
            $query->where('is_featured', true);
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('dashboard.products', compact('products'));
    }

    // ── Create / Store product ────────────────────────────────────────────────
    public function createProduct()
    {
        return view('dashboard.products-form', ['product' => null]);
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_featured' => 'nullable|boolean',
            'is_hot' => 'nullable|boolean',
        ]);
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_hot'] = $request->has('is_hot');
        Product::create(array_merge($validated, ['images' => $this->processImages($request, [])]));

        return redirect()->route('dashboard.products')->with('success', 'Product created.');
    }

    public function editProduct(Product $product)
    {
        return view('dashboard.products-form', compact('product'));
    }

    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_featured' => 'nullable|boolean',
            'is_hot' => 'nullable|boolean',
        ]);
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_hot'] = $request->has('is_hot');

        if ($request->boolean('remove_image')) {
            foreach ($product->all_images as $path) {
                $this->deleteStoredFile($path);
            }
            $images = [];
        } else {
            $images = $this->processImages($request, $product->all_images);
        }
        $product->update(array_merge($validated, ['images' => $images]));

        return redirect()->route('dashboard.products')->with('success', 'Product updated.');
    }

    public function destroyProduct(Product $product)
    {
        foreach ($product->all_images as $path) {
            $this->deleteStoredFile($path);
        }
        $name = $product->name;
        $product->delete();

        return redirect()->route('dashboard.products')->with('success', "Product \"{$name}\" deleted.");
    }

    // ── Orders ────────────────────────────────────────────────────────────────
    public function orders(Request $request)
    {
        $status = $request->input('status');
        $search = trim($request->input('search', ''));

        $query = Order::with(['user', 'items', 'location'])->latest(); // FIX [3]

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->where('order_id', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%"))
            );
        }

        $perPage = AdminSetting::int('dashboard_rows_per_page', 20);
        $orders = $query->paginate($perPage)->withQueryString();

        $statusCounts = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('dashboard.orders', compact('orders', 'statusCounts', 'status', 'search'));
    }

    public function showOrder(Order $order)
    {
        $order->load(['user', 'items.product', 'location']); // FIX [3]

        return view('dashboard.orders-show', compact('order'));
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);
        $order->update(['status' => $request->status]);

        return redirect()->route('dashboard.orders.show', $order)->with('success', 'Order status updated.');
    }

    public function confirmDelivery(Order $order)
    {
        if (! in_array($order->status, ['confirmed', 'processing', 'shipped'])) {
            return redirect()->route('dashboard.orders.show', $order)
                ->with('error', 'Order cannot be marked as delivered from its current status.');
        }
        $order->update(['status' => 'delivered', 'delivery_confirmed_at' => now()]);

        return redirect()->route('dashboard.orders.show', $order)
            ->with('success', "Order #{$order->order_id} marked as delivered ✅");
    }

    // ── Payments ──────────────────────────────────────────────────────────────
    // FIX [4]: payments() method added — referenced in layout.blade nav
    public function payments(Request $request)
    {
        $status = $request->input('status');
        $search = trim($request->input('search', ''));

        $query = Payment::with('order')->latest();

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $query->where(fn ($q) => $q
                ->whereHas('order', fn ($o) => $o->where('order_id', 'like', "%{$search}%"))
                ->orWhere('qr_md5', 'like', "%{$search}%")
                ->orWhere('bakong_hash', 'like', "%{$search}%")
                ->orWhere('from_account_id', 'like', "%{$search}%")
            );
        }

        $perPage = AdminSetting::int('dashboard_rows_per_page', 20);
        $payments = $query->paginate($perPage)->withQueryString();

        $statusCounts = Payment::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $stats = [
            'total_revenue' => Payment::where('status', 'paid')->sum('amount'),
            'paid' => Payment::where('status', 'paid')->count(),
            'pending' => Payment::where('status', 'pending')->count(),
            'expired' => Payment::where('status', 'expired')->count(),
            'failed' => Payment::where('status', 'failed')->count(),
            'manual_pending' => Payment::where('status', 'manual_pending')->count(),
            'refunded' => Payment::where('status', 'refunded')->count(),
            'paid_today' => Payment::where('status', 'paid')->whereDate('paid_at', today())->count(),
        ];

        return view('dashboard.payments', compact('payments', 'statusCounts', 'stats', 'status', 'search'));
    }

    // ── Users ─────────────────────────────────────────────────────────────────
    public function users()
    {
        $users = User::withCount('orders')->latest()->paginate(15);

        return view('dashboard.users', compact('users'));
    }

    // ── Banners ───────────────────────────────────────────────────────────────
    public function banners()
    {
        $banners = Banner::orderBy('order')->get();

        return view('dashboard.banners', compact('banners'));
    }

    // ── Discounts ─────────────────────────────────────────────────────────────
    public function discounts()
    {
        $discounts = Discount::latest()->get();

        return view('dashboard.discounts', compact('discounts'));
    }

    public function discountsStore(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:discounts,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:100',
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->has('is_active');
        $data['categories'] = $request->input('categories', []) ?: null;
        Discount::create($data);

        return redirect()->route('dashboard.discounts')->with('success', 'Discount created.');
    }

    public function discountsUpdate(Request $request, Discount $discount)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:discounts,code,'.$discount->id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:100',
        ]);
        $data['code'] = strtoupper($data['code']);
        $data['is_active'] = $request->has('is_active');
        $data['categories'] = $request->input('categories', []) ?: null;
        $discount->update($data);

        return redirect()->route('dashboard.discounts')->with('success', 'Discount updated.');
    }

    public function discountsSaveBadge(Request $request,Discount $discount)
    {
        // clear_badge=1 means the admin clicked "CLEAR BADGE"
        if ($request->boolean('clear_badge')) {
            $discount->update(['badge_config' => null]);
            return response()->json(['success' => true, 'message' => 'Badge cleared.']);
        }

        $request->validate([
            'badge_config'        => 'required|array',
            'badge_config.text'   => 'required|string|max:30',
            'badge_config.icon'   => 'nullable|string|max:10',
            'badge_config.bg'     => 'nullable|string|max:100',
            'badge_config.border' => 'nullable|string|max:100',
            'badge_config.color'  => 'nullable|string|max:30',
        ]);

        $discount->update([
            'badge_config' => $request->input('badge_config'),
        ]);

        return response()->json(['success' => true, 'message' => 'Badge saved.']);
    }

    public function discountsDestroy(Discount $discount)
    {
        $discount->delete();

        return redirect()->route('dashboard.discounts')->with('success', 'Discount deleted.');
    }

    // ── Full Dashboard Export (all sheets) ───────────────────────────────────
    // Route: GET /dashboard/export
    // Params: from (Y-m-d), to (Y-m-d), format (xlsx|csv)
    //
    // Produces a multi-sheet .xlsx covering:
    //   Summary | Monthly Sales | Daily Sales | Orders |
    //   Order Status | Category Revenue | Top Products | Discounts
    public function dashboardExport(Request $request)
{
    // ── 1. Parse & validate the month inputs (blade sends Y-m) ────────────────
    $fromRaw = $request->input('from', \Carbon\Carbon::now()->subMonth()->format('Y-m'));
    $toRaw   = $request->input('to',   \Carbon\Carbon::now()->format('Y-m'));

    // Validate format: must be Y-m (e.g. "2025-01")
    $monthPattern = '/^\d{4}-(0[1-9]|1[0-2])$/';
    if (!preg_match($monthPattern, $fromRaw) || !preg_match($monthPattern, $toRaw)) {
        return back()->withErrors(['export' => 'Invalid date format. Please use month pickers.']);
    }

    // Convert Y-m → full Y-m-d dates for the export class
    $from = \Carbon\Carbon::createFromFormat('Y-m', $fromRaw)->startOfMonth()->format('Y-m-d');
    $to   = \Carbon\Carbon::createFromFormat('Y-m', $toRaw)->endOfMonth()->format('Y-m-d');

    // Ensure "from" is not after "to"
    if ($from > $to) {
        return back()->withErrors(['export' => '"From" month cannot be after "To" month.']);
    }

    // ── 2. Determine export format ────────────────────────────────────────────
    $format   = in_array($request->input('format'), ['xlsx', 'csv']) ? $request->input('format') : 'xlsx';
    $filename = 'dashboard-' . $from . '-to-' . $to . '.' . $format;

    // ── 3. Stream the download ────────────────────────────────────────────────
    if ($format === 'csv') {
        // CSV does not support multiple sheets — export Summary sheet only.
        // SummarySheet must implement FromCollection/FromQuery + WithHeadings.
        return Excel::download(
            new \App\Exports\Sheets\SummarySheet(
                \Carbon\Carbon::parse($from)->startOfDay(),
                \Carbon\Carbon::parse($to)->endOfDay()
            ),
            $filename,
            \Maatwebsite\Excel\Excel::CSV,
            ['Content-Type' => 'text/csv']
        );
    }

    // Default: full multi-sheet .xlsx
    return Excel::download(new DashboardExport($from, $to), $filename);
}

    // ── Private helpers ───────────────────────────────────────────────────────
    private function processImages(Request $request, array $currentImages): array
    {
        $finalImages = [];

        foreach ($request->input('existing_images', []) as $path) {
            $clean = $this->normalizeImagePath($path);
            if ($clean && ! in_array($clean, $finalImages)) {
                $finalImages[] = $clean;
            }
        }

        $removedPaths = array_diff($currentImages, $request->input('existing_images', []));
        foreach ($removedPaths as $removed) {
            $this->deleteStoredFile($removed);
        }

        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $file) {
                if (! $file->isValid() || count($finalImages) >= 8) {
                    continue;
                }
                $path = $file->store('products', 'public');
                if ($path) {
                    $finalImages[] = '/storage/'.$path;
                }
            }
        }

        foreach ($request->input('image_urls', []) as $url) {
            if (count($finalImages) >= 8) {
                break;
            }
            $url = trim($url);
            if (filter_var($url, FILTER_VALIDATE_URL) && ! in_array($url, $finalImages)) {
                $finalImages[] = $url;
            }
        }

        return array_values(array_slice($finalImages, 0, 8));
    }

    private function normalizeImagePath(string $path): ?string
    {
        $path = trim($path);
        if (empty($path)) {
            return null;
        }

        if (Str::startsWith($path, ['https://', 'http://'])) {
            $appUrl = rtrim(config('app.url'), '/');
            foreach (array_unique([$appUrl, 'http://127.0.0.1:8000', 'http://localhost:8000', 'http://localhost']) as $base) {
                if ($base && Str::startsWith($path, $base.'/')) {
                    return '/'.ltrim(substr($path, strlen($base)), '/');
                }
            }

            return $path;
        }

        return '/'.ltrim($path, '/');
    }

    private function deleteStoredFile(string $path): void
    {
        if (Str::startsWith($path, '/storage/')) {
            Storage::disk('public')->delete(Str::after($path, '/storage/'));
        }
    }

    private function dateFormatExpr(string $column, string $format): string
    {
        $driver = DB::getDriverName();
        $sqlFormat = str_replace(['Y', 'm', 'd'], ['%Y', '%m', '%d'], $format);

        return match ($driver) {
            'pgsql' => "TO_CHAR({$column}, '".str_replace(['%Y', '%m', '%d'], ['YYYY', 'MM', 'DD'], $sqlFormat)."')",
            'sqlite' => "strftime('{$sqlFormat}', {$column})",
            default => "DATE_FORMAT({$column}, '{$sqlFormat}')",
        };
    }
}
