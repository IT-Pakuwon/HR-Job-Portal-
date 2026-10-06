<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Agreement FU's Job tab: the PSM / OLA agreements a follow-up can start from.
 */
class FuJobsExport implements FromCollection, ShouldAutoSize, WithHeadings
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
            'No. PSM / OLA',
            'Company',
            'Tenant No',
            'Trade Name',
            'Property CD',
            'Status',
        ];
    }
}
