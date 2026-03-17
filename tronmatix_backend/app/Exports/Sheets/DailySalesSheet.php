<?php

// app/Exports/Sheets/DailySalesSheet.php

namespace App\Exports\Sheets;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailySalesSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    use BaseSheet;

    public function title(): string { return 'Daily Sales (14d)'; }

    public function headings(): array
    {
        return ['Date', 'Revenue ($)', 'Orders', 'Avg Order Value ($)', 'Discount Saved ($)'];
    }

    public function collection()
    {
        $sqlFmt  = $this->dateExpr('created_at', 'Y-m-d');
        $dailyRows = Order::select(
                DB::raw("{$sqlFmt} as date_key"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('COALESCE(SUM(discount_amount), 0) as discount_saved')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(13)->startOfDay())
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('date_key')
            ->orderBy('date_key')
            ->get()->keyBy('date_key');

        $rows = collect();
        for ($i = 13; $i >= 0; $i--) {
            $date    = Carbon::now()->subDays($i);
            $key     = $date->toDateString();
            $found   = $dailyRows->get($key);
            $revenue = $found ? round((float) $found->revenue, 2) : 0;
            $orders  = $found ? (int) $found->orders : 0;

            $rows->push([
                $date->format('d M Y (D)'),
                $revenue,
                $orders,
                $orders > 0 ? round($revenue / $orders, 2) : 0,
                $found ? round((float) $found->discount_saved, 2) : 0,
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

                $this->applyBaseFormatting($event);
                $this->accentColumn($sheet, 'B', $lastRow);
                $this->accentColumn($sheet, 'E', $lastRow, 'FFA855F7');

                $this->addSummaryRow($sheet, $lastRow, [
                    'TOTAL',
                    "=SUM(B2:B{$lastRow})",
                    "=SUM(C2:C{$lastRow})",
                    "=IFERROR(AVERAGE(D2:D{$lastRow}),0)",
                    "=SUM(E2:E{$lastRow})",
                ]);
            },
        ];
    }

    private function dateExpr(string $col, string $format): string
    {
        $sqlFmt = str_replace(['Y','m','d'], ['%Y','%m','%d'], $format);
        return match (DB::getDriverName()) {
            'pgsql'  => "TO_CHAR({$col}, '" . str_replace(['%Y','%m','%d'], ['YYYY','MM','DD'], $sqlFmt) . "')",
            'sqlite' => "strftime('{$sqlFmt}', {$col})",
            default  => "DATE_FORMAT({$col}, '{$sqlFmt}')",
        };
    }
}
