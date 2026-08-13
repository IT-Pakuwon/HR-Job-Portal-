<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VplLedgerExport implements FromCollection, WithHeadings
{
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
}
