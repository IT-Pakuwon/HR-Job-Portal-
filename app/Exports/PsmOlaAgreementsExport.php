<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * The PSM / OLA and Addendum Active / Completed lists, as shown (same filters).
 */
class PsmOlaAgreementsExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(protected Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Agreement No',
            'Type',
            'Date',
            'Company',
            'Tenant Company Name',
            'Tenant No',
            'Tenant Name',
            'Property Type',
            'Floor',
            'Unit',
            'Floor LOI',
            'Unit LOI',
            'No. PSM / OLA',
            'PSM / OLA Date',
            'PIC Legal',
            'PIC Leasing',
            'Created By',
            'Status',
            'Created From',
        ];
    }
}
