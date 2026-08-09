<?php

namespace App\Exports;

use App\Models\StockMovement;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected Carbon $from;
    protected Carbon $to;
    protected ?string $type;

    public function __construct(string $from = '', string $to = '', ?string $type = null)
    {
        $this->from = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $this->to   = $to   ? Carbon::parse($to)->endOfDay()   : Carbon::now()->endOfDay();
        $this->type = $type;
    }

    public function query()
    {
        $query = StockMovement::query()
            ->with(['createdBy:id,name', 'product:id,name'])
            ->whereBetween('created_at', [$this->from, $this->to])
            ->orderByDesc('created_at');

        if ($this->type) {
            $query->where('type', $this->type);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'DATE',
            'PRODUCT',
            'TYPE',
            'QTY CHANGE',
            'UNIT COST',
            'TOTAL COST',
            'NOTE',
            'PERFORMED BY',
        ];
    }

    /**
     * @param StockMovement $movement
     */
    public function map($movement): array
    {
        return [
            $movement->created_at->format('d M Y H:i'),
            $movement->product?->name ?? ('#'.$movement->product_id),
            strtoupper($movement->type),
            $movement->quantity,
            $movement->unit_cost !== null ? '$'.number_format((float)$movement->unit_cost, 2) : '—',
            $movement->unit_cost !== null ? '$'.number_format((float)$movement->unit_cost * $movement->quantity, 2) : '—',
            $movement->note ?? '—',
            $movement->createdBy?->name ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF374151'],
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
