<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class VplLoyaltyUsageExport implements FromView
{
    public function __construct(
        private array $rows,
        private string $cpnyid,
        private int $year,
        private int $month
    ) {
    }

    public function view(): View
    {
        return view('pages.report-vpl.partials.loyalty-usage-table', [
            'rows'      => $this->rows,
            'cpnyid'    => $this->cpnyid,
            'year'      => $this->year,
            'month'     => $this->month,
            'forExport' => true,
        ]);
    }
}
