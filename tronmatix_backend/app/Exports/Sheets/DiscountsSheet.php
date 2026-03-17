<?php

// app/Exports/Sheets/DiscountsSheet.php

namespace App\Exports\Sheets;

use App\Models\Discount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DiscountsSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    use BaseSheet;

    protected Carbon $from;
    protected Carbon $to;

    public function __construct(Carbon $from, Carbon $to)
    {
        $this->from = $from;
        $this->to   = $to;
    }

    public function title(): string { return 'Discounts'; }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Type',
            'Value',
            'Min Order ($)',
            'Max Uses',
            'Total Used (all time)',
            'Status',
            'Expires At',
            'Categories',
            // Period columns
            'Uses (Period)',
            'Total Saved (Period) ($)',
            'Revenue from Orders ($)',
            'Avg Discount ($)',
            'Period',
        ];
    }

    public function collection()
    {
        return Discount::select(
                'discounts.*',
                DB::raw('COUNT(orders.id)                             AS period_uses'),
                DB::raw('COALESCE(SUM(orders.discount_amount), 0)    AS period_saved'),
                DB::raw('COALESCE(SUM(orders.total), 0)              AS period_revenue'),
                DB::raw('COALESCE(AVG(orders.discount_amount), 0)    AS avg_discount')
            )
            ->leftJoin('orders', function ($join) {
                $join->on('orders.discount_id', '=', 'discounts.id')
                     ->whereBetween('orders.created_at', [$this->from, $this->to]);
            })
            ->groupBy('discounts.id')
            ->orderByDesc('period_saved')
            ->get()
            ->map(fn ($d) => [
                $d->id,
                $d->code,
                strtoupper($d->type),
                $d->type === 'percentage' ? $d->value . '%' : '$' . number_format($d->value, 2),
                $d->min_order  ? '$' . number_format($d->min_order, 2) : '—',
                $d->max_uses   ?? 'Unlimited',
                $d->used_count,
                strtoupper($d->status),
                $d->expires_at ? $d->expires_at->format('d M Y H:i') : 'Never',
                $d->categories ? implode(', ', $d->categories) : 'All',
                (int) $d->period_uses,
                number_format((float) $d->period_saved, 2),
                number_format((float) $d->period_revenue, 2),
                number_format((float) $d->avg_discount, 2),
                $this->from->format('d M Y') . ' – ' . $this->to->format('d M Y'),
            ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $this->applyBaseFormatting($event);

                // Period savings column (L) — purple
                $this->accentColumn($sheet, 'L', $lastRow, 'FFA855F7');
                // Uses column (K) — orange
                $this->accentColumn($sheet, 'K', $lastRow);

                // Status colour-coding (col H)
                $statusColors = [
                    'ACTIVE'    => ['bg' => 'FFD1FAE5', 'fg' => 'FF16A34A'],
                    'EXPIRED'   => ['bg' => 'FFFEE2E2', 'fg' => 'FFDC2626'],
                    'EXHAUSTED' => ['bg' => 'FFFEF3C7', 'fg' => 'FFD97706'],
                    'DISABLED'  => ['bg' => 'FFF3F4F6', 'fg' => 'FF6B7280'],
                ];

                for ($row = 2; $row <= $lastRow; $row++) {
                    $status = strtoupper($sheet->getCell("H{$row}")->getValue());
                    $c      = $statusColors[$status] ?? ['bg' => 'FFF9F9F9', 'fg' => 'FF374151'];
                    $sheet->getStyle("H{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => $c['fg']]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $c['bg']]],
                    ]);
                }

                // Totals row
                $this->addSummaryRow($sheet, $lastRow, [
                    '', 'TOTAL', '', '', '', '',
                    "=SUM(G2:G{$lastRow})",
                    '', '', '',
                    "=SUM(K2:K{$lastRow})",
                    "=SUM(L2:L{$lastRow})",
                    "=SUM(M2:M{$lastRow})",
                    '',
                    '',
                ]);
            },
        ];
    }
}
