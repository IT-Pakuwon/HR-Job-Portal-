<?php

namespace App\Exports;

use App\Exports\Concerns\PrettifiesSheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class VplStockSummaryExport implements FromView, WithEvents
{
    use PrettifiesSheet;

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
            'rows'      => $this->rows,
            'meta'      => $this->meta,
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
                $sheet = $event->sheet->getDelegate();
                $this->prettifySheet($sheet, 2, '4F46E5');

                // Blank spacer columns between Main | Aging | Sources | Used blocks —
                // narrow and unstyled so each section reads as its own band.
                $gapCols = [15];
                $gapCols[] = end($gapCols) + count($this->meta['aging'] ?? []) + 1;
                $gapCols[] = end($gapCols) + count($this->meta['sources'] ?? []) + 1;
                $lastRow = $sheet->getHighestRow();

                foreach ($gapCols as $col) {
                    $letter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getStyle("{$letter}1:{$letter}{$lastRow}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_NONE],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_NONE]],
                    ]);
                    $sheet->getColumnDimension($letter)->setAutoSize(false)->setWidth(2);
                }
            },
        ];
    }
}
