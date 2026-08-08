<?php

// app/Exports/CsvExport.php
// Clean CSV export — no styling, no merged cells, just raw data.
// CSV does not support multiple sheets, so this exports a summary only.

namespace App\Exports;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CsvExport implements FromCollection, WithHeadings, WithTitle
{
    protected Carbon $from;
    protected Carbon $to;

    public function __construct(Carbon $from, Carbon $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function title(): string { return 'Summary'; }

    public function headings(): array
    {
        return ['Metric', 'All Time', 'Period (' . $this->from->format('d M Y') . ' – ' . $this->to->format('d M Y') . ')'];
    }

    public function collection()
    {
        $totalRevenue  = Order::whereNotIn('status', ['cancelled'])->sum('total');
        $periodRevenue = Order::whereNotIn('status', ['cancelled'])
                              ->whereBetween('created_at', [$this->from, $this->to])->sum('total');
        $totalDiscount  = Order::whereNotNull('discount_amount')->where('discount_amount', '>', 0)->sum('discount_amount');
        $periodDiscount = Order::whereNotNull('discount_amount')->where('discount_amount', '>', 0)
                               ->whereBetween('created_at', [$this->from, $this->to])->sum('discount_amount');

        $allOrders    = Order::count();
        $periodOrders = Order::whereBetween('created_at', [$this->from, $this->to])->count();

        return collect([
            ['Total Users',                   User::count(),    User::whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Total Products',                Product::count(), 0],
            ['Total Orders',                  $allOrders,       $periodOrders],
            ['Revenue ($)',                   round($totalRevenue, 2),  round($periodRevenue, 2)],
            ['Avg Order Value ($)',           $allOrders > 0 ? round($totalRevenue / $allOrders, 2) : 0, $periodOrders > 0 ? round($periodRevenue / $periodOrders, 2) : 0],
            ['Pending Orders',                Order::whereIn('status', ['pending', 'confirmed'])->count(), 0],
            ['Active Orders',                 Order::whereIn('status', ['confirmed','processing','shipped'])->count(), 0],
            ['Delivered Orders',              Order::where('status', 'delivered')->count(),  Order::where('status', 'delivered')->whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Cancelled Orders',              Order::where('status', 'cancelled')->count(), Order::where('status', 'cancelled')->whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Discount Saved ($)',            round($totalDiscount, 2),  round($periodDiscount, 2)],
            ['Active Discount Codes',         Discount::active()->count(), 0],
            ['Discount Codes Used (orders)',  Order::whereNotNull('discount_id')->count(), Order::whereNotNull('discount_id')->whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Export Generated',              Carbon::now()->format('d M Y H:i:s'), Carbon::now()->format('d M Y H:i:s')],
        ]);
    }
}
