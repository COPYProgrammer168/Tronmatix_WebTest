<?php

// app/Exports/Sheets/SummarySheet.php

namespace App\Exports\Sheets;

use App\Models\Discount;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummarySheet implements FromCollection, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    use BaseSheet;

    protected Carbon $from;
    protected Carbon $to;

    public function __construct(Carbon $from, Carbon $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function title(): string { return 'Summary'; }

    public function collection()
    {
        $period = $this->from->format('d M Y') . ' – ' . $this->to->format('d M Y');

        $totalRevenue        = Order::whereNotIn('status', ['cancelled'])->sum('total');
        $periodRevenue       = Order::whereNotIn('status', ['cancelled'])
                                    ->whereBetween('created_at', [$this->from, $this->to])->sum('total');
        $totalDiscountUsed   = Order::whereNotNull('discount_amount')->where('discount_amount', '>', 0)->sum('discount_amount');
        $periodDiscountUsed  = Order::whereNotNull('discount_amount')->where('discount_amount', '>', 0)
                                    ->whereBetween('created_at', [$this->from, $this->to])->sum('discount_amount');

        return collect([
            // Header row is row 1 (from headings()) — data starts row 2
            ['METRIC',                        'ALL TIME',                                          'SELECTED PERIOD (' . $period . ')'],
            ['Total Users',                   User::count(),                                       User::whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Total Products',                Product::count(),                                    '—'],
            ['Total Orders',                  Order::count(),                                      Order::whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Revenue ($)',                   number_format($totalRevenue, 2),                    number_format($periodRevenue, 2)],
            ['Pending Orders',                Order::whereIn('status', ['pending', 'confirmed'])->count(), '—'],
            ['Active Orders',                 Order::whereIn('status', ['confirmed','processing','shipped'])->count(), '—'],
            ['Delivered Orders',              Order::where('status', 'delivered')->count(),        Order::where('status', 'delivered')->whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Cancelled Orders',              Order::where('status', 'cancelled')->count(),        Order::where('status', 'cancelled')->whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Total Discount Saved ($)',      number_format($totalDiscountUsed, 2),               number_format($periodDiscountUsed, 2)],
            ['Active Discount Codes',         Discount::active()->count(),                         '—'],
            ['Discount Codes Used (orders)',  Order::whereNotNull('discount_id')->count(),         Order::whereNotNull('discount_id')->whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Export Generated At',           Carbon::now()->format('d M Y H:i:s'),                '—'],
        ]);
    }

    // No separate headings() — first row of collection IS the header
    public function styles(Worksheet $sheet): array
    {
        return [
            // Row 1 = section header labels (col A is "METRIC" etc.)
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A1A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Column A: metric labels — bold dark
            'A' => ['font' => ['bold' => true]],
            // Column B: all-time values — orange
            'B' => ['font' => ['color' => ['argb' => 'FFF97316'], 'bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $sheet->freezePane('A2');
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(36);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(42);

                // Borders
                $sheet->getStyle("A1:C{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFE0E0E0'],
                        ],
                    ],
                ]);

                // Alternate row shading
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF7ED']],
                        ]);
                    }
                }

                // Title above the table
                $sheet->insertNewRowBefore(1, 2);
                $sheet->setCellValue('A1', '📊 TRONMATIX — DASHBOARD EXPORT');
                $sheet->mergeCells('A1:C1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFF97316']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A1A1A']],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);
            },
        ];
    }
}
