<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class VplStockVoucherExport implements FromView
{
    public function __construct(
        private array $groups,
        private int $year,
        private int $month
    ) {
    }

    public function view(): View
    {
        return view('pages.report-vpl.partials.stock-voucher-table', [
            'groups' => $this->groups,
            'year'   => $this->year,
            'month'  => $this->month,
        ]);
    }
}
