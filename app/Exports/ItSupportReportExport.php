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

class ItSupportReportExport implements WithMultipleSheets
{
    public function __construct(private array $data) {}

    protected function moduleLabel(string $module): string
    {
        return match ($module) {
            'RECOMMENDATION' => 'IT Recommendation',
            'ACCESS' => 'Access Request',
            default => 'Ticket Support',
        };
    }

    public function sheets(): array
    {
        $d = $this->data;
        $moduleLabel = $this->moduleLabel($d['module']);

        return [
            // ── Sheet 1: Summary ─────────────────────────────────────────────
            new class($d, $moduleLabel) implements FromArray, WithTitle, WithStyles, WithColumnWidths {
                public function __construct(private array $d, private string $moduleLabel) {}

                public function title(): string
                {
                    return 'Summary';
                }

                public function columnWidths(): array
                {
                    return ['A' => 22, 'B' => 26];
                }

                public function array(): array
                {
                    $s = $this->d['summary'];

                    return [
                        ['IT Support Report'],
                        [],
                        ['Module', $this->moduleLabel],
                        ['Period', $this->d['dateFrom'].' to '.$this->d['dateTo']],
                        ['Company', $this->d['cpnyId'] ?: 'All Companies'],
                        ['Generated', now()->format('d/m/Y H:i')],
                        [],
                        ['Metric', 'Value'],
                        ['Total', $s['total_ticket']],
                        ['Completed', $s['completed']],
                        ['On Progress', $s['on_progress']],
                        ['Completion Rate', $s['completion_rate'].'%'],
                    ];
                }

                public function styles(Worksheet $sheet): void
                {
                    $sheet->getStyle('A1:B1')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3B82F6']],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(30);

                    $sheet->getStyle('A3:A6')->getFont()->setBold(true)->setSize(9);
                    $sheet->getStyle('B3:B6')->getFont()->setSize(9)->getColor()->setARGB('FF475569');

                    $sheet->getStyle('A8:B8')->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF1D4ED8']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF93C5FD']]],
                    ]);
                    $sheet->getRowDimension(8)->setRowHeight(18);

                    $sheet->getStyle('A9:A12')->getFont()->setBold(true);
                    $sheet->getStyle('B9:B12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle('B10:B10')->getFont()->getColor()->setARGB('FF059669');
                    $sheet->getStyle('B11:B11')->getFont()->getColor()->setARGB('FFD97706');

                    $rate = (float) ($this->d['summary']['completion_rate'] ?? 0);
                    $argb = $rate >= 80 ? 'FF059669' : ($rate >= 50 ? 'FFD97706' : 'FFDC2626');
                    $sheet->getStyle('B12:B12')->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => $argb]]]);
                }
            },

            // ── Sheet 2: List ──────────────────────────────────────────────────
            new class($d['tableRows'], $moduleLabel) implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths {
                public function __construct(private $rows, private string $moduleLabel) {}

                public function title(): string
                {
                    return 'List';
                }

                public function headings(): array
                {
                    return ['Doc ID', 'Date', 'Unit/Dept', 'Category', 'Issue/Purpose', 'Status'];
                }

                public function columnWidths(): array
                {
                    return ['A' => 16, 'B' => 12, 'C' => 22, 'D' => 20, 'E' => 45, 'F' => 16];
                }

                public function array(): array
                {
                    return collect($this->rows)->map(fn ($r) => [
                        $r['docid'] ?? '',
                        $r['date'] ?? '',
                        $r['unit'] ?? '',
                        $r['category'] ?? '',
                        $r['issue'] ?? '',
                        $r['status'] ?? '',
                    ])->toArray();
                }

                public function styles(Worksheet $sheet): void
                {
                    $lastRow = $sheet->getHighestRow();

                    $sheet->getStyle('A1:F1')->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3B82F6']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF1D4ED8']]],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(22);

                    if ($lastRow > 1) {
                        for ($row = 2; $row <= $lastRow; $row++) {
                            if ($row % 2 === 0) {
                                $sheet->getStyle('A'.$row.':F'.$row)->getFill()
                                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFF');
                            }
                        }
                        $sheet->getStyle('A1:F'.$lastRow)->getBorders()->getInside()
                            ->setBorderStyle(Border::BORDER_HAIR)->getColor()->setARGB('FFE2E8F0');
                    }
                }
            },
        ];
    }
}
