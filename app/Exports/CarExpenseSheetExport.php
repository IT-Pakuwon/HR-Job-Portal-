<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CarExpenseSheetExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents, WithStyles
{
    protected int   $rowCount    = 0;
    protected float $totalAmount = 0;

    public function __construct(
        protected Request $request,
        protected string  $costTypeId,
        protected string  $costTypeName
    ) {}

    public function title(): string
    {
        // Excel sheet names: max 31 chars, strip invalid chars
        $safe = preg_replace('/[\\\\\/\?\*\[\]:]/', '-', $this->costTypeName);
        return substr($safe, 0, 31);
    }

    public function headings(): array
    {
        return ['Ref No', 'Date', 'Vehicle', 'Driver', 'Cost Type', 'Description', 'Qty', 'Amount'];
    }

    public function array(): array
    {
        $request = $this->request;

        $query = DB::connection('pgsql')
            ->table('tr_car_expense')
            ->whereNull('deleted_at')
            ->where('cost_type', $this->costTypeId)
            ->select(['refnbr', 'ref_date', 'nopol', 'driver', 'cost_descr', 'cost_qty', 'cost_amount']);

        if ($request->date_from) {
            $query->whereDate('ref_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('ref_date', '<=', $request->date_to);
        }

        if ($request->nopol) {
            $query->where('nopol', $request->nopol);
        }

        if ($request->driver) {
            $query->where('driver', 'ilike', "%{$request->driver}%");
        }

        $rows = $query->orderBy('ref_date', 'desc')->get();

        $this->rowCount    = $rows->count();
        $this->totalAmount = (float) $rows->sum('cost_amount');

        return $rows->map(fn ($row) => [
            $row->refnbr    ?? '-',
            $row->ref_date  ? Carbon::parse($row->ref_date)->format('d-M-Y') : '-',
            $row->nopol     ?: '-',
            $row->driver    ?: '-',
            $this->costTypeName,
            $row->cost_descr ?: '-',
            $row->cost_qty   ?? '-',
            (float) ($row->cost_amount ?? 0),
        ])->toArray();
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
                $sheet    = $event->sheet->getDelegate();
                $totalRow = $this->rowCount + 2;

                // Format Amount column as number
                $sheet->getStyle('H2:H' . ($totalRow - 1))
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Total row
                $sheet->setCellValue('A' . $totalRow, 'TOTAL');
                $sheet->mergeCells('A' . $totalRow . ':G' . $totalRow);
                $sheet->setCellValue('H' . $totalRow, $this->totalAmount);
                $sheet->getStyle('H' . $totalRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0');

                $sheet->getStyle('A' . $totalRow . ':H' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFEF9C3'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
                $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            },
        ];
    }
}
