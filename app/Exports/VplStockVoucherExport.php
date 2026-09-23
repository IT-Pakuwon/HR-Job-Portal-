<?php

namespace App\Exports;

use App\Exports\Concerns\PrettifiesSheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromView;

class VplStockVoucherExport implements FromView, WithEvents
{
    use PrettifiesSheet;

    public function __construct(
        private array $groups,
        private string $cpnyid,
        private int $year,
        private int $month
    ) {
    }

    public function view(): View
    {
        return view('pages.report-vpl.partials.stock-voucher-table', [
            'groups'    => $this->groups,
            'cpnyid'    => $this->cpnyid,
            'year'      => $this->year,
            'month'     => $this->month,
            'forExport' => true,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->prettifySheet($event->sheet->getDelegate(), 1, '9333EA');
            },
        ];
    }
}
