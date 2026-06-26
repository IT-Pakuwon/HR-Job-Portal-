<?php

namespace App\Exports;

use App\Models\MsCategory;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CarExpenseTemplateExport implements WithMultipleSheets
{
    protected array $costTypeNames;

    public function __construct()
    {
        $this->costTypeNames = MsCategory::where('groups', 'CAR COST')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->pluck('category_name')
            ->toArray();
    }

    public function sheets(): array
    {
        return [
            new CarExpenseTemplateDataSheet(),
            new CarExpenseTemplateNotesSheet($this->costTypeNames),
        ];
    }
}

class CarExpenseTemplateDataSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function title(): string
    {
        return 'Template';
    }

    public function headings(): array
    {
        return ['DATE', 'NOPOL', 'DRIVER', 'COST_TYPE', 'DESCRIPTION', 'QTY', 'AMOUNT'];
    }

    public function array(): array
    {
        return [
            ['2026-06-26', 'B 1006 SRQ', 'RIKI HALIM', 'BBM', 'Isi bensin', 1, 100000],
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A2:G2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => 'FF94A3B8']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                ]);

                // Tab color green
                $sheet->getTabColor()->setRGB('22c55e');
            },
        ];
    }
}

class CarExpenseTemplateNotesSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(protected array $costTypeNames) {}

    public function title(): string
    {
        return 'Instructions';
    }

    public function array(): array
    {
        $validTypes = implode(', ', $this->costTypeNames);

        return [
            ['#', 'Column', 'Format / Valid Values', 'Required'],
            ['1', 'DATE',        'YYYY-MM-DD  (e.g. 2026-06-26)',   'Yes'],
            ['2', 'NOPOL',       'Vehicle plate number',             'Yes'],
            ['3', 'DRIVER',      'Driver name',                      'Yes'],
            ['4', 'COST_TYPE',   'One of: ' . $validTypes,           'Yes'],
            ['5', 'DESCRIPTION', 'Free text description',            'Yes'],
            ['6', 'QTY',         'Number ≥ 1',                       'Yes'],
            ['7', 'AMOUNT',      'Number ≥ 0 (no thousand separator)', 'Yes'],
            ['', '', '', ''],
            ['', 'IMPORTANT:', 'Delete the example row (row 2) in the Template sheet before importing.', ''],
            ['', 'NOTE:',      'Do NOT add extra rows, notes, or formulas below the data in the Template sheet.', ''],
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Highlight IMPORTANT rows
                foreach ([10, 11] as $r) {
                    $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'italic' => true, 'color' => ['argb' => 'FFDC2626']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF7ED']],
                    ]);
                }

                // Tab color orange
                $sheet->getTabColor()->setRGB('f97316');
            },
        ];
    }
}
