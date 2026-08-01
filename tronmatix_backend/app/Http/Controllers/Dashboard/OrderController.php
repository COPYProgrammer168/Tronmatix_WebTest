<?php

// app/Http/Controllers/Dashboard/OrderController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\TelegramBotService;   // user-facing order notifications
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->input('user');

        $orders = Order::with(['user', 'items', 'location', 'discount'])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->latest()
            ->paginate(20);

        $statusCounts = Order::where(function ($q) use ($userId) {
            if ($userId) $q->where('user_id', $userId);
        })->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalUsers = Order::when($userId, fn($q) => $q->where('user_id', $userId))
            ->distinct()
            ->count('user_id');

        return view('dashboard.orders', compact('orders', 'statusCounts', 'totalUsers', 'userId'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items', 'location', 'discount']);

        return view('dashboard.orders-show', compact('order'));
    }

    public function updateStatus(Request $request, $order_id)
    {
        $order = Order::where('order_id', $order_id)->firstOrFail();
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $request->status]);
        $order->load(['user', 'items']);

        // ── Notify the customer via Telegram ──────────────────────────────────
        // Fires the matching Bot method so the customer gets a real-time
        // Telegram message whenever an admin moves their order forward.
        try {
            $bot = app(TelegramBotService::class);
            match ($request->status) {
                'confirmed'  => $bot->onOrderConfirmed($order),
                'processing' => $bot->onOrderProcessing($order),
                'shipped'    => $bot->onOrderShipped($order),
                'delivered'  => $bot->onOrderDelivered($order),
                'cancelled'  => $bot->onOrderCancelled($order),
                default      => null,
            };
        } catch (\Throwable $e) {
            Log::warning('[Bot] Dashboard status change notify failed: ' . $e->getMessage());
        }

        return redirect()
            ->route('dashboard.orders.show', $order->order_id)
            ->with('success', 'Order status updated to ' . strtoupper($request->status));
    }
}