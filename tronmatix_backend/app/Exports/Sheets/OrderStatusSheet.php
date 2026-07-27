<?php

// app/Exports/Sheets/OrderStatusSheet.php

namespace App\Exports\Sheets;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderStatusSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithEvents
{
    use BaseSheet;

    public function title(): string { return 'Order Status'; }

    public function headings(): array
    {
        return ['Status', 'Count', '% of Total', 'Revenue', 'Avg Order Value'];
    }

    public function collection()
    {
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
        $total    = Order::count() ?: 1;

        $rows = collect();
        foreach ($statuses as $status) {
            $count   = Order::where('status', $status)->count();
            $revenue = $status !== 'cancelled'
                ? round((float) Order::where('status', $status)->sum('total'), 2)
                : 0.0;
            $avg     = $count > 0 && $status !== 'cancelled' ? round($revenue / $count, 2) : 0;

            $rows->push([
                strtoupper($status),
                $count,
                $count / $total,
                $revenue,
                $avg,
            ]);
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $this->addSheetTitle($event, '🥧 Order Status Breakdown');
                $lastRow = $sheet->getHighestRow();

                $this->applyBaseFormatting($event, 2);
                $this->accentColumn($sheet, 'D', $lastRow);

                // Number formats
                $this->setIntFormat($sheet, 'B', $lastRow);
                $this->setPercentFormat($sheet, 'C', $lastRow);
                $this->setCurrencyFormat($sheet, 'D', $lastRow);
                $this->setCurrencyFormat($sheet, 'E', $lastRow);

                // Colour-code status column
                $colorMap = [
                    'DELIVERED'  => ['bg' => 'FFD1FAE5', 'fg' => 'FF16A34A'],
                    'SHIPPED'    => ['bg' => 'FFDBEAFE', 'fg' => 'FF2563EB'],
                    'PROCESSING' => ['bg' => 'FFE0F2FE', 'fg' => 'FF0891B2'],
                    'CONFIRMED'  => ['bg' => 'FFEDE9FE', 'fg' => 'FF7C3AED'],
                    'CANCELLED'  => ['bg' => 'FFFEE2E2', 'fg' => 'FFDC2626'],
                    'PENDING'    => ['bg' => 'FFFEF3C7', 'fg' => 'FFD97706'],
                ];

                for ($row = 3; $row <= $lastRow; $row++) {
                    $status = strtoupper($sheet->getCell("A{$row}")->getValue());
                    $c      = $colorMap[$status] ?? ['bg' => 'FFF9F9F9', 'fg' => 'FF374151'];
                    $sheet->getStyle("A{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => $c['fg']]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $c['bg']]],
                    ]);
                }

                $this->addSummaryRow($event, $lastRow, [
                    'TOTAL',
                    "=SUM(B4:B{$lastRow})",
                    1.0,
                    "=SUM(D4:D{$lastRow})",
                    '',
                ]);
            },
        ];
    }
}
