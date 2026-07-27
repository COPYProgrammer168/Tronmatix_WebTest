<?php

// app/Exports/PdfExport.php
// Generates a professional PDF report with full styling using barryvdh/laravel-dompdf.
// Requires: composer require barryvdh/laravel-dompdf

namespace App\Exports;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PdfExport
{
    protected Carbon $from;
    protected Carbon $to;

    public function __construct(Carbon $from, Carbon $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function download(): \Illuminate\Http\Response
    {
        $data = $this->buildData();
        $data['generated_at'] = Carbon::now()->format('d F Y \a\t H:i');

        $pdf = Pdf::loadView('exports.report-pdf', $data);
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'dejavu-sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
        ]);

        $filename = 'tronmatix-report-'
            . $this->from->format('Y-m') . '-to-' . $this->to->format('Y-m')
            . '.pdf';

        return $pdf->download($filename);
    }

    private function buildData(): array
    {
        $period = $this->from->format('d M Y') . ' – ' . $this->to->format('d M Y');

        $totalRevenue  = Order::whereNotIn('status', ['cancelled'])->sum('total');
        $periodRevenue = Order::whereNotIn('status', ['cancelled'])
                              ->whereBetween('created_at', [$this->from, $this->to])->sum('total');
        $totalDiscount  = Order::whereNotNull('discount_amount')->where('discount_amount', '>', 0)->sum('discount_amount');
        $periodDiscount = Order::whereNotNull('discount_amount')->where('discount_amount', '>', 0)
                               ->whereBetween('created_at', [$this->from, $this->to])->sum('discount_amount');

        $allOrders    = Order::count();
        $periodOrders = Order::whereBetween('created_at', [$this->from, $this->to])->count();

        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $statusData = [];
        foreach ($statuses as $status) {
            $count = Order::where('status', $status)->count();
            $revenue = $status !== 'cancelled'
                ? round((float) Order::where('status', $status)->sum('total'), 2)
                : 0;
            $statusData[] = [
                'label' => ucfirst($status),
                'count' => $count,
                'pct'   => $allOrders > 0 ? round($count / $allOrders * 100, 1) : 0,
                'revenue' => $revenue,
            ];
        }

        // Monthly trend (last 12 months)
        $monthlySales = [];
        $salesRows = Order::select(
                DB::raw("DATE_TRUNC('month', created_at)::date as month"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('COALESCE(SUM(discount_amount), 0) as discount_saved')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->whereNotIn('status', ['cancelled'])
            ->groupBy(DB::raw("DATE_TRUNC('month', created_at)::date"))
            ->orderBy(DB::raw("DATE_TRUNC('month', created_at)::date"))
            ->get()->keyBy(function ($r) {
                return Carbon::parse($r->month)->format('Y-m');
            });

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $key  = $date->format('Y-m');
            $s    = $salesRows->get($key);
            $monthlySales[] = [
                'label'   => $date->format('M Y'),
                'revenue' => $s ? round((float) $s->revenue, 2) : 0,
                'orders'  => $s ? (int) $s->orders : 0,
                'saved'   => $s ? round((float) $s->discount_saved, 2) : 0,
            ];
        }

        // Top products (top 10)
        $topProducts = Product::select(
                'products.name',
                'products.category',
                'products.brand',
                DB::raw('COALESCE(SUM(order_items.qty), 0) as units_sold'),
                DB::raw('COALESCE(SUM(order_items.price * order_items.qty), 0) as item_revenue')
            )
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', fn ($j) => $j->on('order_items.order_id', '=', 'orders.id')
                ->whereNotIn('orders.status', ['cancelled']))
            ->groupBy('products.id', 'products.name', 'products.category', 'products.brand')
            ->orderByDesc('units_sold')
            ->limit(10)
            ->get();

        // Category breakdown
        $categoryRevenue = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotIn('orders.status', ['cancelled'])
            ->select('products.category',
                DB::raw('SUM(order_items.qty) as units_sold'),
                DB::raw('SUM(order_items.price * order_items.qty) as revenue'))
            ->groupBy('products.category')
            ->orderByDesc('revenue')
            ->get();

        $grandTotalRevenue = $categoryRevenue->sum('revenue') ?: 1;

        return compact(
            'period', 'totalRevenue', 'periodRevenue', 'totalDiscount', 'periodDiscount',
            'allOrders', 'periodOrders', 'statusData', 'monthlySales',
            'topProducts', 'categoryRevenue', 'grandTotalRevenue',
        ) + ['from' => $this->from, 'to' => $this->to];
    }
}
