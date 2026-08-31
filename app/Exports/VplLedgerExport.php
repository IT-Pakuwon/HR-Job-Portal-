<?php

namespace App\Exports;

use App\Exports\Concerns\PrettifiesSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class VplLedgerExport implements FromCollection, WithHeadings, WithEvents
{
    use PrettifiesSheet;

    public function __construct(private Collection $rows)
    {
    }

    public function headings(): array
    {
        return [
            'Ref No', 'CreateDate', 'CpnyID', 'Type', 'PostDate',
            'Product ID', 'Expired Date', 'Product Name', 'Qty',
            'Reference Refnbr', 'Purpose', 'Warehouse',
        ];
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->prettifySheet($event->sheet->getDelegate(), 1, '2563EB', null);
            },
        ];
    }
}
