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

        $totalRevenue  = Order::whereNotIn('status', ['cancelled'])->sum('total') ?? '—';
        $periodRevenue = Order::whereNotIn('status', ['cancelled'])
                              ->whereBetween('created_at', [$this->from, $this->to])->sum('total') ?? '—' ;
        $totalDiscount  = Order::whereNotNull('discount_amount')->where('discount_amount', '>', 0)->sum('discount_amount') ?? '—';
        $periodDiscount = Order::whereNotNull('discount_amount')->where('discount_amount', '>', 0)
                               ->whereBetween('created_at', [$this->from, $this->to])->sum('discount_amount') ?? '—';

        $allOrders     = Order::count() ?? '—';
        $periodOrders  = Order::whereBetween('created_at', [$this->from, $this->to])->count() ?? '—';

        return collect([
            ['METRIC',                        'ALL TIME',       'PERIOD (' . $period . ')'],
            ['Total Users',                   User::count(),    User::whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Total Products',                Product::count(), 0],
            ['Total Orders',                  $allOrders,       $periodOrders],
            ['Revenue ($)',                   round($totalRevenue, 2),  round($periodRevenue, 2)],
            ['Avg Order Value ($)',           $allOrders > 0 ? round($totalRevenue / $allOrders, 2) : 0, $periodOrders > 0 ? round($periodRevenue / $periodOrders, 2) : 0],
            ['Pending Orders',                Order::whereIn('status', ['pending', 'confirmed'])->count(), 0],
            ['Active Orders',                 Order::whereIn('status', ['confirmed','processing','shipped'])->count(), 0],
            ['Delivered Orders',              Order::where('status', 'delivered')->count(), Order::where('status', 'delivered')->whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Cancelled Orders',              Order::where('status', 'cancelled')->count(), Order::where('status', 'cancelled')->whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Discount Saved ($)',            round($totalDiscount, 2),  round($periodDiscount, 2)],
            ['Active Discount Codes',         Discount::active()->count(), 0],
            ['Discount Codes Used (orders)',  Order::whereNotNull('discount_id')->count(), Order::whereNotNull('discount_id')->whereBetween('created_at', [$this->from, $this->to])->count()],
            ['Export Generated',              Carbon::now()->format('d M Y H:i:s'), Carbon::now()->format('d M Y H:i:s')],
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF'], 'name' => 'Calibri'],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E1E2E']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            'A' => ['font' => ['bold' => true, 'name' => 'Calibri', 'size' => 11]],
            'B' => ['font' => ['color' => ['argb' => 'FFF97316'], 'bold' => true, 'name' => 'Calibri']],
            'C' => ['font' => ['color' => ['argb' => 'FF3B82F6'], 'bold' => true, 'name' => 'Calibri']],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $this->addSheetTitle($event, '📊 TRONMATIX — Dashboard Summary', 'Auto-generated ' . Carbon::now()->format('d F Y H:i'));

                // Re-get row count after title insertion
                $lastRow = $sheet->getHighestRow();

                $sheet->freezePane('A4');
                $sheet->getRowDimension(3)->setRowHeight(22);

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(38);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(42);

                // Borders (data starts at row 3 after title)
                $sheet->getStyle("A3:C{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFE2E0DD'],
                        ],
                    ],
                ]);

                // Alternate row shading
                for ($row = 4; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF7ED']],
                        ]);
                    }
                }

                // Number format for currency values
                $currencyRows = [5, 10]; // Revenue, Discount Saved
                foreach ($currencyRows as $row) {
                    $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("C{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                }

                // Number format for count rows
                for ($row = 4; $row <= $lastRow; $row++) {
                    if (!in_array($row, $currencyRows)) {
                        $sheet->getStyle("B{$row}:C{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    }
                }

                // Export generated row — timestamp
                $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => 'FF888888'], 'name' => 'Calibri', 'size' => 10],
                ]);
            },
        ];
    }
}
