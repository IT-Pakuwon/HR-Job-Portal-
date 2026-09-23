<?php

namespace App\Exports;

use App\Exports\Concerns\PrettifiesSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class VplProductStockExport implements FromCollection, WithHeadings, WithEvents
{
    use PrettifiesSheet;

    public function __construct(private Collection $rows)
    {
    }

    public function headings(): array
    {
        return ['Company', 'Product ID', 'Expired Date', 'Name', 'Value', 'Uom', 'Warehouse', 'Stock'];
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->prettifySheet($event->sheet->getDelegate(), 1, '7C3AED', null);
            },
        ];
    }
}
