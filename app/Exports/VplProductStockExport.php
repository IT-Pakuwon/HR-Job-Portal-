<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VplProductStockExport implements FromCollection, WithHeadings
{
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
}
