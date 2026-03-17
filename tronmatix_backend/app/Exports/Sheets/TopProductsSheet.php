<?php

// app/Exports/Sheets/TopProductsSheet.php

namespace App\Exports\Sheets;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TopProductsSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    use BaseSheet;

    public function title(): string { return 'Top Products'; }

    public function headings(): array
    {
        return [
            'Rank',
            'Product Name',
            'Category',
            'Brand',
            'Price ($)',
            'Stock',
            'Units Sold',
            'Total Revenue ($)',
            'Hot',
            'Featured',
        ];
    }

    public function collection()
    {
        // Full product list sorted by units sold — not limited to top 5
        $products = Product::select(
                'products.*',
                DB::raw('COALESCE(SUM(order_items.qty), 0) as units_sold'),
                DB::raw('COALESCE(SUM(order_items.price * order_items.qty), 0) as item_revenue')
            )
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                     ->whereNotIn('orders.status', ['cancelled']);
            })
            ->groupBy('products.id')
            ->orderByDesc('units_sold')
            ->get();

        return $products->map(fn ($p, $i) => [
            $i + 1,
            $p->name,
            $p->category ?? '—',
            $p->brand    ?? '—',
            number_format((float) $p->price, 2),
            $p->stock ?? 0,
            (int) $p->units_sold,
            number_format((float) $p->item_revenue, 2),
            $p->is_hot      ? '✓' : '',
            $p->is_featured ? '✓' : '',
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $this->applyBaseFormatting($event);
                $this->accentColumn($sheet, 'G', $lastRow);            // Units Sold
                $this->accentColumn($sheet, 'H', $lastRow);            // Revenue

                // Low stock highlight (col F)
                for ($row = 2; $row <= $lastRow; $row++) {
                    $stock = (int) $sheet->getCell("F{$row}")->getValue();
                    if ($stock <= 0) {
                        $sheet->getStyle("F{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFDC2626']],
                        ]);
                    } elseif ($stock <= 5) {
                        $sheet->getStyle("F{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFD97706']],
                        ]);
                    }
                }

                $this->addSummaryRow($sheet, $lastRow, [
                    '', 'TOTAL', '', '', '',
                    "=SUM(F2:F{$lastRow})",
                    "=SUM(G2:G{$lastRow})",
                    "=SUM(H2:H{$lastRow})",
                    '', '',
                ]);
            },
        ];
    }
}
