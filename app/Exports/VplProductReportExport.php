<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class VplProductReportExport implements FromView
{
    public function __construct(
        private array $groups,
        private string $cpnyid,
        private int $year,
        private int $month
    ) {
    }

    public function view(): View
    {
        return view('pages.report-vpl.partials.product-report-table', [
            'groups' => $this->groups,
            'cpnyid' => $this->cpnyid,
            'year'   => $this->year,
            'month'  => $this->month,
        ]);
    }
}
