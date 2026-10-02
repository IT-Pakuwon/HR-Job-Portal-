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

/**
 * Multi-sheet export for the Training Report dashboard: a Summary sheet
 * (stat cards + department/level breakdown) and a Sessions sheet listing
 * every training session in the filtered period — mirrors
 * TrainingReportController::gatherExportData() so the export always matches
 * what's on screen.
 */
class TrainingReportExport implements WithMultipleSheets
{
    public function __construct(private array $data) {}

    public function sheets(): array
    {
        $d = $this->data;

        return [
            // ── Sheet 1: Summary ─────────────────────────────────────────────
            new class($d) implements FromArray, WithTitle, WithStyles, WithColumnWidths {
                public function __construct(private array $d) {}

                public function title(): string
                {
                    return 'Summary';
                }

                public function columnWidths(): array
                {
                    return ['A' => 28, 'B' => 18];
                }

                public function array(): array
                {
                    $s = $this->d['summary'];

                    $rows = [
                        ['Training Report'],
                        [],
                        ['Period', $this->d['dateFrom'].' to '.$this->d['dateTo']],
                        ['Company', $this->d['cpnyId'] ?: 'All Companies'],
                        ['Generated', now()->format('d/m/Y H:i')],
                        [],
                        ['Metric', 'Value'],
                        ['Total Attendance', $s['total_attendance']],
                        ['Total Sessions', $s['total_sessions']],
                        ['Total Training Hours', $s['total_training_hours']],
                        ['Avg. Satisfaction (/5)', $s['avg_satisfaction'] ?? '–'],
                        ['Avg. Stars (/5)', $s['avg_stars']],
                        [],
                        ['By Department', 'Attendance'],
                    ];

                    foreach ($this->d['byDepartment'] as $name => $count) {
                        $rows[] = [$name, $count];
                    }

                    $rows[] = [];
                    $rows[] = ['By Level/Grade', 'Attendance'];

                    foreach ($this->d['byLevel'] as $name => $count) {
                        $rows[] = [$name, $count];
                    }

                    return $rows;
                }

                public function styles(Worksheet $sheet): void
                {
                    $sheet->getStyle('A1:B1')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3B82F6']],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(30);

                    $sheet->getStyle('A3:A5')->getFont()->setBold(true)->setSize(9);
                    $sheet->getStyle('B3:B5')->getFont()->setSize(9)->getColor()->setARGB('FF475569');

                    $sheet->getStyle('A7:B7')->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF1D4ED8']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF93C5FD']]],
                    ]);

                    $sheet->getStyle('A8:A12')->getFont()->setBold(true);
                    $sheet->getStyle('B8:B12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }
            },

            // ── Sheet 2: Sessions ──────────────────────────────────────────────
            new class($d['sessionRows']) implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths {
                public function __construct(private $rows) {}

                public function title(): string
                {
                    return 'Sessions';
                }

                public function headings(): array
                {
                    return ['Date', 'Training', 'Level', 'Attendees', 'Total Hours', 'Avg. Satisfaction', 'Avg. Stars'];
                }

                public function columnWidths(): array
                {
                    return ['A' => 14, 'B' => 36, 'C' => 20, 'D' => 12, 'E' => 12, 'F' => 16, 'G' => 12];
                }

                public function array(): array
                {
                    return collect($this->rows)->map(fn ($r) => [
                        $r['date'] ?? '',
                        $r['training_name'] ?? '',
                        $r['level_name'] ?? '',
                        $r['attendees'] ?? 0,
                        $r['total_hours'] ?? 0,
                        $r['avg_satisfaction'] ?? '–',
                        $r['avg_stars'] ?? 0,
                    ])->toArray();
                }

                public function styles(Worksheet $sheet): void
                {
                    $lastRow = $sheet->getHighestRow();

                    $sheet->getStyle('A1:G1')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3B82F6']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1D4ED8']]],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(22);

                    if ($lastRow > 1) {
                        for ($row = 2; $row <= $lastRow; $row++) {
                            if ($row % 2 === 0) {
                                $sheet->getStyle('A'.$row.':G'.$row)->getFill()
                                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFF');
                            }
                        }
                        $sheet->getStyle('A1:G'.$lastRow)->getBorders()->getInside()
                            ->setBorderStyle(Border::BORDER_HAIR)->getColor()->setARGB('FFE2E8F0');
                    }
                }
            },
        ];
    }
}
