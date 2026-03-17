<?php

// app/Http/Controllers/Dashboard/OrderController.php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

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

        return redirect()
            ->route('dashboard.orders.show', $order)
            ->with('success', 'Order status updated to '.strtoupper($request->status));
    }
}
