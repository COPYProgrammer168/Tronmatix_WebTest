<?php

// app/Exports/DashboardExport.php
// Requires: composer require maatwebsite/excel
//
// Produces a single .xlsx with 8 sheets:
//   1. Summary          — all stat cards
//   2. Monthly Sales    — last 12 months revenue + orders + new users
//   3. Daily Sales      — last 14 days revenue
//   4. Orders           — all orders (date-range filtered)
//   5. Order Status     — status breakdown counts
//   6. Category Revenue — revenue per product category
//   7. Top Products     — best-selling products
//   8. Discounts        — all discount codes + monthly usage

namespace App\Exports;


use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DashboardExport implements WithMultipleSheets
{
    use Exportable;

    protected Carbon $from;
    protected Carbon $to;

    /**
     * @param string $from  Y-m-d  (defaults to start of current month)
     * @param string $to    Y-m-d  (defaults to today)
     */
    public function __construct(string $from = '', string $to = '')
    {
        $this->from = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->startOfMonth();
        $this->to = $to ? Carbon::parse($to)->endOfDay() : Carbon::now()->endOfDay();
    }

    public function sheets(): array
    {
        return [
            new Sheets\SummarySheet($this->from, $this->to),
            new Sheets\MonthlySalesSheet(),
            new Sheets\DailySalesSheet(),
            new Sheets\OrdersSheet($this->from, $this->to),
            new Sheets\OrderStatusSheet(),
            new Sheets\CategoryRevenueSheet(),
            new Sheets\TopProductsSheet(),
            new Sheets\DiscountsSheet($this->from, $this->to),
        ];
    }
}
