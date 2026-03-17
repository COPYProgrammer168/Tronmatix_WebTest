<?php

// app/Exports/Sheets/OrdersSheet.php

namespace App\Exports\Sheets;

use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    use BaseSheet;

    protected Carbon $from;
    protected Carbon $to;

    public function __construct(Carbon $from, Carbon $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function title(): string { return 'Orders'; }

    // FromQuery — memory-safe for large datasets (processes in chunks)
    public function query()
    {
        return Order::with(['user', 'location'])
            ->whereBetween('created_at', [$this->from, $this->to])
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Customer',
            'Email',
            'Phone',
            'Status',
            'Subtotal ($)',
            'Discount Code',
            'Discount Amount ($)',
            'Total ($)',
            'Payment Method',
            'Delivery Address',
            'Items Count',
            'Date',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_id,
            $order->user?->username ?? $order->user?->name ?? 'Guest',
            $order->user?->email ?? '—',
            $order->user?->phone ?? $order->location?->phone ?? '—',
            strtoupper($order->status),
            number_format((float) ($order->subtotal ?? $order->total), 2),
            $order->discount_code ?? '—',
            number_format((float) ($order->discount_amount ?? 0), 2),
            number_format((float) $order->total, 2),
            $order->payment_method ?? '—',
            collect([$order->location?->address, $order->location?->city, $order->location?->country])
                ->filter()->implode(', ') ?: '—',
            $order->items?->count() ?? 0,
            $order->created_at->format('d M Y H:i'),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $this->applyBaseFormatting($event);

                // Total column (I) — orange bold
                $this->accentColumn($sheet, 'I', $lastRow);
                // Discount amount (H) — purple
                $this->accentColumn($sheet, 'H', $lastRow, 'FFA855F7');

                // Status column — colour-code text
                for ($row = 2; $row <= $lastRow; $row++) {
                    $status = strtolower($sheet->getCell("E{$row}")->getValue());
                    $color  = match ($status) {
                        'delivered'  => 'FF16A34A',
                        'shipped'    => 'FF2563EB',
                        'processing' => 'FF0891B2',
                        'confirmed'  => 'FF7C3AED',
                        'cancelled'  => 'FFDC2626',
                        default      => 'FFD97706', // pending
                    };
                    $sheet->getStyle("E{$row}")->getFont()->getColor()->setARGB($color);
                    $sheet->getStyle("E{$row}")->getFont()->setBold(true);
                }

                // Totals row
                $this->addSummaryRow($sheet, $lastRow, [
                    'TOTAL',
                    '', '', '', '',
                    "=SUM(F2:F{$lastRow})",
                    '',
                    "=SUM(H2:H{$lastRow})",
                    "=SUM(I2:I{$lastRow})",
                    '', '', '',
                    '',
                ]);
            },
        ];
    }
}
