<?php

// app/Exports/Sheets/MonthlySalesSheet.php

namespace App\Exports\Sheets;

use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonthlySalesSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithEvents
{
    use BaseSheet;

    public function title(): string { return 'Monthly Sales'; }

    public function headings(): array
    {
        return ['Month', 'Revenue', 'Orders', 'New Users', 'Avg Order Value', 'Discount Saved'];
    }

    public function collection()
    {
        $driver = DB::getDriverName();

        $salesRows = Order::select(
                DB::raw($this->dateExpr('created_at', 'Y-m') . ' as month_key'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('COALESCE(SUM(discount_amount), 0) as discount_saved')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()->keyBy('month_key');

        $userRows = User::select(
                DB::raw($this->dateExpr('created_at', 'Y-m') . ' as month_key'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('month_key')
            ->get()->keyBy('month_key');

        $rows = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date    = Carbon::now()->subMonths($i);
            $key     = $date->format('Y-m');
            $sale    = $salesRows->get($key);
            $users   = $userRows->get($key);

            $revenue = $sale ? round((float) $sale->revenue, 2) : 0;
            $orders  = $sale ? (int) $sale->orders : 0;
            $avgOV   = $orders > 0 ? round($revenue / $orders, 2) : 0;

            $rows->push([
                $date->format('M Y'),
                $revenue,
                $orders,
                $users ? (int) $users->total : 0,
                $avgOV,
                $sale ? round((float) $sale->discount_saved, 2) : 0,
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

                $this->addSheetTitle($event, '📈 Monthly Sales Trend (12 Months)');
                // Re-get lastRow after title insert (addSheetTitle inserts 2 rows)
                $lastRow = $sheet->getHighestRow();

                $this->applyBaseFormatting($event, 2);
                $this->accentColumn($sheet, 'B', $lastRow);             // Revenue
                $this->accentColumn($sheet, 'F', $lastRow, 'FFA855F7'); // Discount

                // Currency formatting
                $this->setCurrencyFormat($sheet, 'B', $lastRow);
                $this->setCurrencyFormat($sheet, 'E', $lastRow);
                $this->setCurrencyFormat($sheet, 'F', $lastRow);

                // Integer formatting
                $this->setIntFormat($sheet, 'C', $lastRow);
                $this->setIntFormat($sheet, 'D', $lastRow);

                // Totals
                $this->addSummaryRow($event, $lastRow, [
                    'TOTAL',
                    "=SUM(B3:B{$lastRow})",
                    "=SUM(C3:C{$lastRow})",
                    "=SUM(D3:D{$lastRow})",
                    "=IFERROR(AVERAGE(E3:E{$lastRow}),0)",
                    "=SUM(F3:F{$lastRow})",
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
