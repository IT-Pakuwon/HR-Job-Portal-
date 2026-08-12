<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class VplStockSummaryExport implements FromView
{
    public function __construct(
        private array $rows,
        private array $meta,
        private string $cpnyid,
        private int $year,
        private int $month
    ) {
    }

    public function view(): View
    {
        return view('pages.report-vpl.partials.stock-summary-table', [
            'rows'   => $this->rows,
            'meta'   => $this->meta,
            'cpnyid' => $this->cpnyid,
            'year'   => $this->year,
            'month'  => $this->month,
        ]);
    }
}
