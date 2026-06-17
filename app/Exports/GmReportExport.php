<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GmReportExport implements WithMultipleSheets
{
    public function __construct(private array $data) {}

    public function sheets(): array
    {
        $d = $this->data;

        return [
            // ── Sheet 1: Summary ─────────────────────────────────────────────
            new class($d) implements FromArray, WithTitle, WithStyles, WithColumnWidths {
                public function __construct(private array $d) {}

                public function title(): string { return 'Summary'; }

                public function columnWidths(): array
                {
                    return ['A' => 22, 'B' => 26, 'C' => 26];
                }

                public function array(): array
                {
                    $s = $this->d['summary'];
                    return [
                        ['GM Report Dashboard'],
                        [],
                        ['Period',    $this->d['dateFrom'] . ' to ' . $this->d['dateTo']],
                        ['Company',   $this->d['cpnyId'] ?: 'All Companies'],
                        ['Generated', now()->format('d/m/Y H:i')],
                        [],
                        ['Metric', 'Amount (IDR)', 'Formatted'],
                        ['Total Budget',    round($s['total_budget']),    'Rp ' . number_format(round($s['total_budget']),    0, ',', '.')],
                        ['Total Used',      round($s['total_used']),      'Rp ' . number_format(round($s['total_used']),      0, ',', '.')],
                        ['Total Reserved',  round($s['total_reserve']),   'Rp ' . number_format(round($s['total_reserve']),   0, ',', '.')],
                        ['Total Remaining', round($s['total_remaining']), 'Rp ' . number_format(round($s['total_remaining']), 0, ',', '.')],
                        ['Utilization %',   $s['utilization_pct'],        $s['utilization_pct'] . '%'],
                    ];
                }

                public function styles(Worksheet $sheet): void
                {
                    // Title row
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7C3AED']],
                    ]);
                    $sheet->getStyle('A1:C1')->getFill()
                        ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF7C3AED');
                    $sheet->getRowDimension(1)->setRowHeight(30);

                    // Meta rows
                    $sheet->getStyle('A3:A5')->getFont()->setBold(true)->setSize(9);
                    $sheet->getStyle('B3:B5')->getFont()->setSize(9)->getColor()->setARGB('FF475569');

                    // Column-header row (row 7)
                    $sheet->getStyle('A7:C7')->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF4C1D95']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEDE9FE']],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFC4B5FD']]],
                    ]);
                    $sheet->getRowDimension(7)->setRowHeight(18);

                    // Data rows 8–12
                    $sheet->getStyle('A8:A12')->getFont()->setBold(true);
                    $sheet->getStyle('B8:B12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Color-code the utilization value (row 12)
                    $util = (float) ($this->d['summary']['utilization_pct'] ?? 0);
                    $argb = $util >= 80 ? 'FFDC2626' : ($util >= 60 ? 'FFD97706' : 'FF059669');
                    $sheet->getStyle('B12:C12')->getFont()->getColor()->setARGB($argb);
                    $sheet->getStyle('B12:C12')->getFont()->setBold(true);

                    // Remaining row green (row 11)
                    $sheet->getStyle('B11:C11')->getFont()->getColor()->setARGB('FF059669');

                    // Reserved row amber (row 10)
                    $sheet->getStyle('B10:C10')->getFont()->getColor()->setARGB('FFD97706');

                    // Alternating background for data rows
                    foreach ([9, 11] as $r) {
                        $sheet->getStyle('A' . $r . ':C' . $r)->getFill()
                            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFAFAFA');
                    }

                    // Bottom border on last data row
                    $sheet->getStyle('A12:C12')->getBorders()->getBottom()
                        ->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setARGB('FFC4B5FD');
                }
            },

            // ── Sheet 2: By Department ────────────────────────────────────────
            new class($d['deptRows'], $d['dateFrom'], $d['dateTo']) implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths {
                public function __construct(private $rows, private string $from, private string $to) {}

                public function title(): string { return 'By Department'; }

                public function headings(): array
                {
                    return ['Department', 'Budget (IDR)', 'Used (IDR)', 'Reserved (IDR)', 'Remaining (IDR)', 'Usage %'];
                }

                public function columnWidths(): array
                {
                    return ['A' => 32, 'B' => 22, 'C' => 22, 'D' => 22, 'E' => 22, 'F' => 12];
                }

                public function array(): array
                {
                    $rows = collect($this->rows);
                    $data = $rows->map(fn ($r) => [
                        $r->department_fin_id ?? '',
                        round((float) ($r->total_final     ?? 0)),
                        round((float) ($r->total_used      ?? 0)),
                        round((float) ($r->total_reserve   ?? 0)),
                        round((float) ($r->total_remaining ?? 0)),
                        (float) ($r->used_pct ?? 0),
                    ])->toArray();

                    // Totals row
                    if ($rows->isNotEmpty()) {
                        $tf = $rows->sum(fn ($r) => (float)($r->total_final     ?? 0));
                        $tu = $rows->sum(fn ($r) => (float)($r->total_used      ?? 0));
                        $tv = $rows->sum(fn ($r) => (float)($r->total_reserve   ?? 0));
                        $tr = $rows->sum(fn ($r) => (float)($r->total_remaining ?? 0));
                        $data[] = ['TOTAL', round($tf), round($tu), round($tv), round($tr), $tf > 0 ? round($tu / $tf * 100, 1) : 0];
                    }

                    return $data;
                }

                public function styles(Worksheet $sheet): void
                {
                    $lastRow = $sheet->getHighestRow();

                    // Header row
                    $sheet->getStyle('A1:F1')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7C3AED']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF5B21B6']]],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(22);

                    if ($lastRow > 1) {
                        $dataEnd = $lastRow - 1; // last data row (before totals)

                        // Bold department names
                        $sheet->getStyle('A2:A' . $dataEnd)->getFont()->setBold(true);

                        // Right-align numeric columns
                        $sheet->getStyle('B1:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                        // Reserved column amber
                        $sheet->getStyle('D2:D' . $dataEnd)->getFont()->getColor()->setARGB('FFB45309');

                        // Remaining column green
                        $sheet->getStyle('E2:E' . $dataEnd)->getFont()->getColor()->setARGB('FF047857');

                        // Alternating row shading
                        for ($row = 2; $row <= $dataEnd; $row++) {
                            if ($row % 2 === 0) {
                                $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
                                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFAF5FF');
                            }
                        }

                        // Color usage % column based on value
                        for ($row = 2; $row <= $dataEnd; $row++) {
                            $pct  = (float) ($sheet->getCell('F' . $row)->getValue() ?? 0);
                            $argb = $pct >= 80 ? 'FFDC2626' : ($pct >= 60 ? 'FFD97706' : 'FF059669');
                            $sheet->getStyle('F' . $row)->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['argb' => $argb]],
                            ]);
                        }

                        // Totals row
                        $sheet->getStyle('A' . $lastRow . ':F' . $lastRow)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF4C1D95']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEDE9FE']],
                            'borders' => [
                                'top'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFC4B5FD']],
                                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFC4B5FD']],
                            ],
                        ]);

                        // Thin border under every row
                        $sheet->getStyle('A1:F' . $lastRow)->getBorders()->getInside()
                            ->setBorderStyle(Border::BORDER_HAIR)->getColor()->setARGB('FFE2E8F0');
                    }
                }
            },

            // ── Sheet 3: By Activity ──────────────────────────────────────────
            new class($d['actRows']) implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths {
                public function __construct(private $rows) {}

                public function title(): string { return 'By Activity'; }

                public function headings(): array
                {
                    return ['Activity', 'Budget (IDR)', 'Used (IDR)', 'Reserved (IDR)', 'Remaining (IDR)', 'Usage %'];
                }

                public function columnWidths(): array
                {
                    return ['A' => 40, 'B' => 22, 'C' => 22, 'D' => 22, 'E' => 22, 'F' => 12];
                }

                public function array(): array
                {
                    $rows = collect($this->rows);
                    $data = $rows->map(fn ($r) => [
                        $r->activity_descr ?? $r->activity_id ?? '',
                        round((float) ($r->total_final     ?? 0)),
                        round((float) ($r->total_used      ?? 0)),
                        round((float) ($r->total_reserve   ?? 0)),
                        round((float) ($r->total_remaining ?? 0)),
                        (float) ($r->used_pct ?? 0),
                    ])->toArray();

                    if ($rows->isNotEmpty()) {
                        $tf = $rows->sum(fn ($r) => (float)($r->total_final     ?? 0));
                        $tu = $rows->sum(fn ($r) => (float)($r->total_used      ?? 0));
                        $tv = $rows->sum(fn ($r) => (float)($r->total_reserve   ?? 0));
                        $tr = $rows->sum(fn ($r) => (float)($r->total_remaining ?? 0));
                        $data[] = ['TOTAL', round($tf), round($tu), round($tv), round($tr), $tf > 0 ? round($tu / $tf * 100, 1) : 0];
                    }

                    return $data;
                }

                public function styles(Worksheet $sheet): void
                {
                    $lastRow = $sheet->getHighestRow();

                    // Header (teal theme for activity)
                    $sheet->getStyle('A1:F1')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0891B2']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF0E7490']]],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(22);

                    if ($lastRow > 1) {
                        $dataEnd = $lastRow - 1;

                        $sheet->getStyle('A2:A' . $dataEnd)->getFont()->setBold(true);
                        $sheet->getStyle('B1:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getStyle('D2:D' . $dataEnd)->getFont()->getColor()->setARGB('FFB45309');
                        $sheet->getStyle('E2:E' . $dataEnd)->getFont()->getColor()->setARGB('FF047857');

                        for ($row = 2; $row <= $dataEnd; $row++) {
                            if ($row % 2 === 0) {
                                $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
                                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0FDFF');
                            }
                            $pct  = (float) ($sheet->getCell('F' . $row)->getValue() ?? 0);
                            $argb = $pct >= 80 ? 'FFDC2626' : ($pct >= 60 ? 'FFD97706' : 'FF059669');
                            $sheet->getStyle('F' . $row)->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['argb' => $argb]],
                            ]);
                        }

                        $sheet->getStyle('A' . $lastRow . ':F' . $lastRow)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF0E7490']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0F9FF']],
                            'borders' => [
                                'top'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF67E8F9']],
                                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF67E8F9']],
                            ],
                        ]);

                        $sheet->getStyle('A1:F' . $lastRow)->getBorders()->getInside()
                            ->setBorderStyle(Border::BORDER_HAIR)->getColor()->setARGB('FFE2E8F0');
                    }
                }
            },

            // ── Sheet 4: Monthly Trend ────────────────────────────────────────
            new class($d['monthRows'], $d['year'], $d['totalBudget']) implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths {
                public function __construct(
                    private array $rows,
                    private string $year,
                    private float $totalBudget,
                ) {}

                public function title(): string { return 'Monthly Trend'; }

                public function headings(): array
                {
                    return ['Month', 'Year', 'Monthly Used (IDR)', 'Cumulative Used (IDR)'];
                }

                public function columnWidths(): array
                {
                    return ['A' => 12, 'B' => 10, 'C' => 26, 'D' => 26];
                }

                public function array(): array
                {
                    return array_map(fn ($r) => [
                        $r['month'],
                        $this->year,
                        $r['used'],
                        $r['cumulative'],
                    ], $this->rows);
                }

                public function styles(Worksheet $sheet): void
                {
                    $lastRow = $sheet->getHighestRow();

                    // Header (amber/orange theme for monthly)
                    $sheet->getStyle('A1:D1')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD97706']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFB45309']]],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(22);

                    if ($lastRow > 1) {
                        $sheet->getStyle('A2:A' . $lastRow)->getFont()->setBold(true);
                        $sheet->getStyle('C1:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                        // Cumulative column purple/violet
                        $sheet->getStyle('D2:D' . $lastRow)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF7C3AED']],
                        ]);

                        // Alternating shading
                        for ($row = 2; $row <= $lastRow; $row++) {
                            if ($row % 2 === 0) {
                                $sheet->getStyle('A' . $row . ':D' . $row)->getFill()
                                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEFCE8');
                            }
                        }

                        $sheet->getStyle('A1:D' . $lastRow)->getBorders()->getInside()
                            ->setBorderStyle(Border::BORDER_HAIR)->getColor()->setARGB('FFE2E8F0');

                        // Note row after data
                        $noteRow = $lastRow + 2;
                        $sheet->setCellValue('A' . $noteRow, 'Total Annual Budget: Rp ' . number_format($this->totalBudget, 0, ',', '.'));
                        $sheet->getStyle('A' . $noteRow)->getFont()->setItalic(true)->getColor()->setARGB('FF64748B');
                    }
                }
            },
        ];
    }
}
