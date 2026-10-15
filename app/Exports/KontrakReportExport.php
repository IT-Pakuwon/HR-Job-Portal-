<?php

namespace App\Exports;

use App\Http\Controllers\ReportKontrakController;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KontrakReportExport extends StringValueBinder implements FromCollection, WithHeadings, WithStyles, WithCustomValueBinder
{
    public function __construct(private Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return array_merge(['Kontrak ID', 'Cpny', 'Nama Vendor', 'Pekerjaan', 'Periode Kontrak', 'Tahun'], ReportKontrakController::MONTHS);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('G2');
        $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true)->setVertical('top');
        foreach (range('A', 'R') as $column) {
            $sheet->getColumnDimension($column)->setWidth(30);
        }
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(55);
        $sheet->getColumnDimension('E')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(10);

        return [1 => ['font' => ['bold' => true]]];
    }
}
