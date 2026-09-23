<?php

namespace App\Exports;

use App\Exports\Concerns\PrettifiesSheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromView;

class VplStockOutVoucherExport implements FromView, WithEvents
{
    use PrettifiesSheet;

    public function __construct(
        private array $rows,
        private string $cpnyid,
        private int $year,
        private int $month,
        private string $whsLabel
    ) {
    }

    public function view(): View
    {
        return view('pages.report-vpl.partials.stock-out-voucher-table', [
            'rows'      => $this->rows,
            'cpnyid'    => $this->cpnyid,
            'year'      => $this->year,
            'month'     => $this->month,
            'whsLabel'  => $this->whsLabel,
            'forExport' => true,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->prettifySheet($event->sheet->getDelegate(), 1, 'B45309');
            },
        ];
    }
}
