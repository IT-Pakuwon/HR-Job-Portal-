<?php

namespace App\Exports;

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

class LuckydrawParticipantTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new LuckydrawParticipantTemplateDataSheet(),
            new LuckydrawParticipantTemplateNotesSheet(),
        ];
    }
}

class LuckydrawParticipantTemplateDataSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function title(): string
    {
        return 'Template';
    }

    public function headings(): array
    {
        return ['CUSTOMER_NAME', 'COMPANY_NAME', 'REF_NBR'];
    }

    public function array(): array
    {
        return [
            ['John Doe', 'PT Contoh Sejahtera', 'PG00012345'],
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A2:C2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => 'FF94A3B8']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                ]);

                $sheet->getTabColor()->setRGB('6366f1');
            },
        ];
    }
}

class LuckydrawParticipantTemplateNotesSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    private int $importantRow1 = 0;
    private int $importantRow2 = 0;

    public function title(): string
    {
        return 'Instructions';
    }

    public function array(): array
    {
        $rows = [
            ['#', 'Column', 'Format / Notes', 'Required'],
            ['1', 'CUSTOMER_NAME', 'Full name of the participant', 'Yes'],
            ['2', 'COMPANY_NAME', 'Company / affiliation of the participant', 'No'],
            ['3', 'REF_NBR', 'Unique reference — e.g. PG card number or phone number', 'Yes'],
            ['', '', '', ''],
        ];

        $this->importantRow1 = count($rows) + 1;
        $rows[] = ['', 'IMPORTANT:', 'Delete the example row (row 2) in the Template sheet before importing.', ''];
        $this->importantRow2 = count($rows) + 1;
        $rows[] = ['', 'NOTE:', 'A customer can appear on multiple rows — each row is one extra entry (more draw chances).', ''];

        return $rows;
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach ([$this->importantRow1, $this->importantRow2] as $r) {
                    $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'italic' => true, 'color' => ['argb' => 'FFDC2626']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF7ED']],
                    ]);
                }

                $sheet->getTabColor()->setRGB('f97316');
            },
        ];
    }
}
