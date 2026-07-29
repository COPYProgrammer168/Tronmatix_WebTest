<?php

// app/Exports/Sheets/CategoryRevenueSheet.php

namespace App\Exports\Sheets;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CategoryRevenueSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithEvents
{
    use BaseSheet;

    public function title(): string { return 'Category Revenue'; }

    public function headings(): array
    {
        return ['Category', 'Units Sold', 'Revenue', '% of Total', 'Avg Unit Price'];
    }

    public function collection()
    {
        $rows = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders',   'order_items.order_id',   '=', 'orders.id')
            ->whereNotIn('orders.status', ['cancelled'])
            ->select(
                'products.category',
                DB::raw('SUM(order_items.qty) as units_sold'),
                DB::raw('SUM(order_items.price * order_items.qty) as revenue')
            )
            ->groupBy('products.category')
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = $rows->sum('revenue') ?: 1;

        return $rows->map(fn ($r) => [
            $r->category,
            (int) $r->units_sold,
            round((float) $r->revenue, 2),
            $r->revenue / $totalRevenue,
            $r->units_sold > 0 ? round($r->revenue / $r->units_sold, 2) : 0,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $this->addSheetTitle($event, '🍩 Revenue by Category');
                $lastRow = $sheet->getHighestRow();

                $this->applyBaseFormatting($event, 3);
                $this->accentColumn($sheet, 'C', $lastRow);

                // Number formats
                $this->setIntFormat($sheet, 'B', $lastRow);
                $this->setCurrencyFormat($sheet, 'C', $lastRow);
                $this->setPercentFormat($sheet, 'D', $lastRow);
                $this->setCurrencyFormat($sheet, 'E', $lastRow);

                $this->addSummaryRow($event, $lastRow, [
                    'TOTAL',
                    "=SUM(B3:B{$lastRow})",
                    "=SUM(C3:C{$lastRow})",
                    1.0,
                    '',
                ]);
            },
        ];
    }
}
