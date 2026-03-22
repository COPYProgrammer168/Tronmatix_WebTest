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
    public function index()
    {
        $orders = Order::with(['user', 'items', 'location'])  // ← location eager-loaded
            ->latest()
            ->paginate(20);

        return view('dashboard.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items', 'location']);

        return view('dashboard.orders-show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
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
            ->route('dashboard.orders.show', $order)
            ->with('success', 'Order status updated to ' . strtoupper($request->status));
    }
}
