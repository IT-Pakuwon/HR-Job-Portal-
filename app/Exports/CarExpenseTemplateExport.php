<?php

namespace App\Exports;

use App\Models\MsCategory;
use App\Models\MsCompany;
use App\Models\MsDepartment;
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
    protected array $companyData;
    protected array $departmentData;
    protected string $exampleCpnyId;
    protected string $exampleDeptId;

    public function __construct()
    {
        $this->costTypeNames = MsCategory::where('groups', 'CAR COST')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->pluck('category_name')
            ->toArray();

        $companies = MsCompany::where('status', 'A')
            ->orderBy('cpny_name')
            ->get(['cpny_id', 'cpny_name']);

        $this->companyData   = $companies->map(fn ($c) => ['id' => $c->cpny_id, 'name' => $c->cpny_name])->toArray();
        $this->exampleCpnyId = $companies->first()?->cpny_id ?? 'CPNY001';

        $departments = MsDepartment::where('status', 'A')
            ->orderBy('department_name')
            ->get(['department_id', 'department_name']);

        $this->departmentData = $departments->map(fn ($d) => ['id' => $d->department_id, 'name' => $d->department_name])->toArray();
        $this->exampleDeptId  = $departments->first()?->department_id ?? 'DEPT001';
    }

    public function sheets(): array
    {
        return [
            new CarExpenseTemplateDataSheet($this->exampleCpnyId, $this->exampleDeptId),
            new CarExpenseTemplateNotesSheet($this->costTypeNames, $this->companyData, $this->departmentData),
        ];
    }
}

class CarExpenseTemplateDataSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    public function __construct(
        protected string $exampleCpnyId,
        protected string $exampleDeptId,
    ) {}

    public function title(): string
    {
        return 'Template';
    }

    public function headings(): array
    {
        return ['DATE', 'COMPANY_ID', 'DEPARTMENT_ID', 'NOPOL', 'DRIVER', 'KILOMETER', 'COST_TYPE', 'DESCRIPTION', 'QTY', 'AMOUNT'];
    }

    public function array(): array
    {
        return [
            ['2026-06-26', $this->exampleCpnyId, $this->exampleDeptId, 'B 1006 SRQ', 'RIKI HALIM', 150000, 'BBM', 'Isi bensin', 1, 100000],
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

                $sheet->getStyle('A2:J2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => 'FF94A3B8']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8FAFC']],
                ]);

                $sheet->getTabColor()->setRGB('22c55e');
            },
        ];
    }
}

class CarExpenseTemplateNotesSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    private int $importantRow1   = 0;
    private int $importantRow2   = 0;
    private int $companyHeaderRow = 0;
    private int $deptHeaderRow    = 0;

    public function __construct(
        protected array $costTypeNames,
        protected array $companyData,
        protected array $departmentData,
    ) {}

    public function title(): string
    {
        return 'Instructions';
    }

    public function array(): array
    {
        $validTypes = implode(', ', $this->costTypeNames);

        $rows = [
            ['#', 'Column', 'Format / Valid Values', 'Required'],
            ['1', 'DATE',          'YYYY-MM-DD  (e.g. 2026-06-26)',                  'Yes'],
            ['2', 'COMPANY_ID',    'Company ID — see Company Reference below',       'Yes'],
            ['3', 'DEPARTMENT_ID', 'Department ID — see Department Reference below', 'Yes'],
            ['4', 'NOPOL',         'Vehicle plate number',                           'Yes'],
            ['5', 'DRIVER',        'Driver name',                                    'Yes'],
            ['6', 'KILOMETER',     'Odometer reading (number ≥ 0)',                  'No'],
            ['7', 'COST_TYPE',     'One of: ' . $validTypes,                         'Yes'],
            ['8', 'DESCRIPTION',   'Free text description',                          'Yes'],
            ['9', 'QTY',           'Number ≥ 1',                                     'Yes'],
            ['10', 'AMOUNT',       'Number ≥ 0 (no thousand separator)',             'Yes'],
            ['', '', '', ''],
        ];

        $this->importantRow1 = count($rows) + 1;
        $rows[] = ['', 'IMPORTANT:', 'Delete the example row (row 2) in the Template sheet before importing.', ''];
        $this->importantRow2 = count($rows) + 1;
        $rows[] = ['', 'NOTE:', 'Do NOT add extra rows, notes, or formulas below the data in the Template sheet.', ''];

        $rows[] = ['', '', '', ''];

        $this->companyHeaderRow = count($rows) + 1;
        $rows[] = ['COMPANY REFERENCE', '', '', ''];
        $rows[] = ['ID', 'Name', '', ''];
        foreach ($this->companyData as $c) {
            $rows[] = [$c['id'], $c['name'], '', ''];
        }

        $rows[] = ['', '', '', ''];

        $this->deptHeaderRow = count($rows) + 1;
        $rows[] = ['DEPARTMENT REFERENCE', '', '', ''];
        $rows[] = ['ID', 'Name', '', ''];
        foreach ($this->departmentData as $d) {
            $rows[] = [$d['id'], $d['name'], '', ''];
        }

        return $rows;
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

                foreach ([$this->importantRow1, $this->importantRow2] as $r) {
                    $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'italic' => true, 'color' => ['argb' => 'FFDC2626']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF7ED']],
                    ]);
                }

                foreach ([$this->companyHeaderRow, $this->deptHeaderRow] as $r) {
                    $sheet->getStyle("A{$r}:D{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF1E293B']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
                    ]);
                    $subRow = $r + 1;
                    $sheet->getStyle("A{$subRow}:D{$subRow}")->applyFromArray([
                        'font' => ['bold' => true],
                    ]);
                }

                $sheet->getTabColor()->setRGB('f97316');
            },
        ];
    }
}
