<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Shared "make it look like a real report, not a raw HTML dump" pass for
 * Excel exports: bold/colored header, borders, right-aligned numbers,
 * highlighted category & grand-total rows, frozen header, autosized columns.
 */
trait PrettifiesSheet
{
    private function prettifySheet(Worksheet $sheet, int $headerRows, string $accentColor, ?string $grandTotalLabel = 'Grand Total'): void
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumnLetter = $sheet->getHighestColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumnLetter);

        if ($highestRow < 1) {
            return;
        }

        $sheet->setShowGridlines(false);

        $headerRange = "A1:{$highestColumnLetter}{$headerRows}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $accentColor]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        for ($r = 1; $r <= $headerRows; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(24);
        }

        if ($highestRow > $headerRows) {
            $bodyRange = 'A'.($headerRows + 1).":{$highestColumnLetter}{$highestRow}";
            $sheet->getStyle($bodyRange)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $colLetter = Coordinate::stringFromColumnIndex($col);
                for ($row = $headerRows + 1; $row <= $highestRow; $row++) {
                    $cell = $sheet->getCell($colLetter.$row);
                    $value = $cell->getValue();
                    if ($value === null || $value === '') {
                        continue;
                    }
                    if (is_int($value) || is_float($value)) {
                        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $cell->getStyle()->getNumberFormat()->setFormatCode(
                            floor($value) == $value ? '#,##0' : '#,##0.00'
                        );
                    } elseif (is_string($value) && $this->looksNumeric($value)) {
                        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }
                }
            }
        }

        // Full-width merged rows below the header are category banners — band them.
        foreach ($sheet->getMergeCells() as $mergeRange) {
            [$start, $end] = explode(':', $mergeRange);
            $startRowNum = (int) preg_replace('/\D/', '', $start);
            if ($startRowNum <= $headerRows) {
                continue;
            }

            $startCol = Coordinate::columnIndexFromString(preg_replace('/\d/', '', $start));
            $endCol = Coordinate::columnIndexFromString(preg_replace('/\d/', '', $end));
            $span = $endCol - $startCol + 1;

            if ($span >= max(2, (int) round($highestColumnIndex * 0.5))) {
                $sheet->getStyle($mergeRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => $accentColor]],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $this->tint($accentColor)]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
            }
        }

        // Last row that says "Grand Total" gets bolded and set off with a top border.
        if ($grandTotalLabel) {
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $value = $sheet->getCell(Coordinate::stringFromColumnIndex($col).$highestRow)->getValue();
                if (is_string($value) && stripos($value, $grandTotalLabel) !== false) {
                    $range = "A{$highestRow}:{$highestColumnLetter}{$highestRow}";
                    $sheet->getStyle($range)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                        'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $accentColor]]],
                    ]);
                    break;
                }
            }
        }

        $sheet->freezePane('A'.($headerRows + 1));
        $sheet->setAutoFilter("A{$headerRows}:{$highestColumnLetter}{$headerRows}");

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $headerRows);
    }

    private function looksNumeric(string $value): bool
    {
        $trimmed = trim($value);
        if ($trimmed === '' || $trimmed === '-') {
            return false;
        }

        return (bool) preg_match('/^-?(Rp\s?)?[\d.,]+%?$/', $trimmed);
    }

    private function tint(string $hex): string
    {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $mix = fn ($c) => (int) round($c + (255 - $c) * 0.85);

        return sprintf('%02X%02X%02X', $mix($r), $mix($g), $mix($b));
    }
}
