<?php

// app/Exports/Sheets/BaseSheet.php
// Professional styling trait — shared by all dashboard export sheets.

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait BaseSheet
{
    // ── Brand colours ──────────────────────────────────────────────────────────
    private const BRAND_ORANGE = 'FFF97316';
    private const BRAND_DARK   = 'FF1E1E2E';
    private const BRAND_WHITE  = 'FFFFFFFF';
    private const ROW_EVEN     = 'FFF8F6F3';
    private const BORDER_COLOR = 'FFE2E0DD';
    private const HEADER_BG    = 'FF374151'; // Slightly lighter dark for headers without subtitle

    // ── Apply consistent borders, row shading, freeze, auto-filter ─────────────
    protected function applyBaseFormatting(AfterSheet $event, int $headerRow = 2): void
    {
        $sheet   = $event->sheet->getDelegate();
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();

        // Header row styling — applied via AfterSheet event for reliable persistence
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
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
        ]);

        // Freeze header row
        $sheet->freezePane('A' . ($headerRow + 1));

        // Auto-filter
        $sheet->setAutoFilter("A{$headerRow}:{$lastCol}{$headerRow}");

        // Header row height
        $sheet->getRowDimension($headerRow)->setRowHeight(24);

        // Apply Calibri to all data cells
        $sheet->getStyle("A" . ($headerRow + 1) . ":{$lastCol}{$lastRow}")->applyFromArray([
            'font' => [
                'name'  => 'Calibri',
                'size'  => 11,
                'color' => ['argb' => 'FF333333'],
            ],
        ]);

        // Thin borders on all data
        if ($lastRow > $headerRow) {
            $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => self::BORDER_COLOR],
                    ],
                ],
            ]);
        }

        // Alternate row shading
        for ($row = $headerRow + 1; $row <= $lastRow; $row++) {
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => self::ROW_EVEN],
                    ],
                ]);
            }
        }
    }

    // ── Branded title row (inserted at the top of every sheet) ─────────────────
    protected function addSheetTitle(AfterSheet $event, string $title, string $subtitle = ''): void
    {
        $sheet   = $event->sheet->getDelegate();
        $lastCol = $sheet->getHighestColumn();

        $sheet->insertNewRowBefore(1, 2);

        // Main title
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 14,
                'color' => ['argb' => self::BRAND_ORANGE],
                'name'  => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::BRAND_DARK],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Subtitle
        if ($subtitle) {
            $sheet->setCellValue('A2', $subtitle);
            $sheet->mergeCells("A2:{$lastCol}2");
            $sheet->getStyle('A2')->applyFromArray([
                'font' => [
                    'size'  => 10,
                    'color' => ['argb' => 'FF888888'],
                    'name'  => 'Calibri',
                    'italic' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF5F3F0'],
                ],
            ]);
            $sheet->getRowDimension(2)->setRowHeight(20);

            // Adjust the actual header row to row 3
            $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'size'  => 11,
                    'color' => ['argb' => self::BRAND_WHITE],
                    'name'  => 'Calibri',
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => self::BRAND_DARK],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->freezePane('A4');
        } else {
            // No subtitle: style header row at row 2 with white font
            $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'size'  => 11,
                    'color' => ['argb' => self::BRAND_WHITE],
                    'name'  => 'Calibri',
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => self::HEADER_BG],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $sheet->freezePane('A3');
        }
    }

    // ── Orange accent on a specific column ─────────────────────────────────────
    protected function accentColumn(Worksheet $sheet, string $col, int $lastRow, string $argb = null, int $dataStartRow = 3): void
    {
        $argb = $argb ?? self::BRAND_ORANGE;
        if ($lastRow < $dataStartRow) {
            return;
        }
        $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => $argb]],
        ]);
    }

    // ── Right-align a column for numbers ──────────────────────────────────────
    protected function alignRight(Worksheet $sheet, string $col, int $lastRow, int $dataStartRow = 3): void
    {
        if ($lastRow < $dataStartRow) {
            return;
        }
        $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$lastRow}")
              ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    // ── Apply Excel currency format to a column ────────────────────────────────
    protected function setCurrencyFormat(Worksheet $sheet, string $col, int $lastRow, int $dataStartRow = 3): void
    {
        if ($lastRow < $dataStartRow) {
            return;
        }
        $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$lastRow}")
              ->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $this->alignRight($sheet, $col, $lastRow, $dataStartRow);
    }

    // ── Apply integer format to a column ───────────────────────────────────────
    protected function setIntFormat(Worksheet $sheet, string $col, int $lastRow, int $dataStartRow = 3): void
    {
        if ($lastRow < $dataStartRow) {
            return;
        }
        $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$lastRow}")
              ->getNumberFormat()->setFormatCode('#,##0');
        $this->alignRight($sheet, $col, $lastRow, $dataStartRow);
    }

    // ── Apply percentage format to a column ────────────────────────────────────
    protected function setPercentFormat(Worksheet $sheet, string $col, int $lastRow, int $dataStartRow = 3): void
    {
        if ($lastRow < $dataStartRow) {
            return;
        }
        $sheet->getStyle("{$col}{$dataStartRow}:{$col}{$lastRow}")
              ->getNumberFormat()->setFormatCode('0.0%');
        $this->alignRight($sheet, $col, $lastRow, $dataStartRow);
    }

    // ── Summary totals row with dark background + orange text ──────────────────
    protected function addSummaryRow(AfterSheet $event, int $lastRow, array $summaryData): void
    {
        $sheet     = $event->sheet->getDelegate();
        $summaryRow = $lastRow + 2;
        $col       = 'A';
        $lastCol   = $sheet->getHighestColumn();

        foreach ($summaryData as $value) {
            $sheet->setCellValue("{$col}{$summaryRow}", $value);
            $col++;
        }

        $sheet->getStyle("A{$summaryRow}:{$lastCol}{$summaryRow}")->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 11,
                'color' => ['argb' => self::BRAND_ORANGE],
                'name'  => 'Calibri',
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => self::BRAND_DARK],
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color'       => ['argb' => self::BRAND_ORANGE],
                ],
            ],
        ]);

        $sheet->getRowDimension($summaryRow)->setRowHeight(22);
    }
}
