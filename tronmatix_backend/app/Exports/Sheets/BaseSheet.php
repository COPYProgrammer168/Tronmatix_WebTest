<?php

// app/Exports/Sheets/BaseSheet.php
// Shared styling helpers inherited by all dashboard sheets.

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait BaseSheet
{
    // ── Consistent header style ───────────────────────────────────────────────
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold'  => true,
                    'size'  => 11,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A1A1A'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    // ── Apply borders + freeze + auto-filter to any sheet ────────────────────
    protected function applyBaseFormatting(AfterSheet $event): void
    {
        $sheet   = $event->sheet->getDelegate();
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();

        // Freeze header row
        $sheet->freezePane('A2');

        // Auto-filter
        $sheet->setAutoFilter("A1:{$lastCol}1");

        // Header row height
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Thin borders on all data
        if ($lastRow > 1) {
            $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFE0E0E0'],
                    ],
                ],
            ]);
        }

        // Alternate row shading for readability
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF9F9F9'],
                    ],
                ]);
            }
        }
    }

    // ── Orange accent on a specific column (1-based index) ───────────────────
    protected function accentColumn(Worksheet $sheet, string $col, int $lastRow, string $argb = 'FFF97316'): void
    {
        if ($lastRow < 2) {
            return;
        }
        $sheet->getStyle("{$col}2:{$col}{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => $argb]],
        ]);
    }

    // ── Summary totals row at the bottom ─────────────────────────────────────
    protected function addSummaryRow(Worksheet $sheet, int $lastRow, array $summaryData): void
    {
        $summaryRow = $lastRow + 2;
        $col        = 'A';

        foreach ($summaryData as $value) {
            $sheet->setCellValue("{$col}{$summaryRow}", $value);
            $col++;
        }

        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle("A{$summaryRow}:{$lastCol}{$summaryRow}")->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFF97316'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1A1A1A'],
            ],
        ]);
    }
}
