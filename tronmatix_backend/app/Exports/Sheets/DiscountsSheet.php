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
            'ID', 'Code', 'Type', 'Value', 'Min Order',
            'Max Uses', 'Total Used', 'Status', 'Expires At',
            'Categories',
            'Uses (Period)', 'Saved (Period)', 'Revenue (Period)', 'Avg Discount',
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
                $d->type === 'percentage' ? $d->value . '%' : round($d->value, 2),
                $d->min_order ?? 0,
                $d->max_uses   ?? 0,
                $d->used_count ?? 0,
                strtoupper($d->status),
                $d->expires_at ? $d->expires_at->format('d M Y') : 'Never',
                $d->categories ? implode(', ', $d->categories) : 'All',
                (int) $d->period_uses,
                round((float) $d->period_saved, 2),
                round((float) $d->period_revenue, 2),
                round((float) $d->avg_discount, 2),
            ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $this->addSheetTitle($event, '🏷️ Discount Codes — Period Analysis',
                    $this->from->format('d M Y') . ' – ' . $this->to->format('d M Y'));
                $lastRow = $sheet->getHighestRow();

                $this->applyBaseFormatting($event, 3);

                // Period savings (L) — purple
                $this->accentColumn($sheet, 'L', $lastRow, 'FFA855F7', 4);
                $this->setCurrencyFormat($sheet, 'L', $lastRow, 4);
                $this->setCurrencyFormat($sheet, 'M', $lastRow, 4);
                $this->setCurrencyFormat($sheet, 'N', $lastRow, 4);

                // Uses columns — integer
                $this->setIntFormat($sheet, 'K', $lastRow, 4);
                $this->setIntFormat($sheet, 'G', $lastRow, 4);
                $this->setIntFormat($sheet, 'F', $lastRow, 4);

                // Min order (E) — currency
                $this->setCurrencyFormat($sheet, 'E', $lastRow, 4);

                // Status colour-coding
                $statusColors = [
                    'ACTIVE'    => ['bg' => 'FFD1FAE5', 'fg' => 'FF16A34A'],
                    'EXPIRED'   => ['bg' => 'FFFEE2E2', 'fg' => 'FFDC2626'],
                    'EXHAUSTED' => ['bg' => 'FFFEF3C7', 'fg' => 'FFD97706'],
                    'DISABLED'  => ['bg' => 'FFF3F4F6', 'fg' => 'FF6B7280'],
                ];

                for ($row = 4; $row <= $lastRow; $row++) {
                    $status = strtoupper($sheet->getCell("H{$row}")->getValue());
                    $c      = $statusColors[$status] ?? ['bg' => 'FFF9F9F9', 'fg' => 'FF374151'];
                    $sheet->getStyle("H{$row}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => $c['fg']]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $c['bg']]],
                    ]);
                }

                $this->addSummaryRow($event, $lastRow, [
                    '', 'TOTAL', '', '', '', '',
                    "=SUM(G4:G{$lastRow})", '', '', '',
                    "=SUM(K4:K{$lastRow})",
                    "=SUM(L4:L{$lastRow})",
                    "=SUM(M4:M{$lastRow})",
                    '',
                ]);
            },
        ];
    }
}
