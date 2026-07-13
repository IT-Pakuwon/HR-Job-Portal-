<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CsDetailExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    private const FIXED_HEADINGS = [
        'Inventory ID', 'Inventory Descr', 'Note', 'Qty', 'UOM',
        'Budget Department', 'COA', 'Div', 'Dept', 'COA Description', 'Last Price',
    ];

    private int $headerRow;

    private int $lastDataRow;

    private int $summaryStartRow;

    public function __construct(
        private $cs,
        private $detail,
        private array $vendors,
        private ?string $docUrl = null
    ) {}

    public function title(): string
    {
        return substr((string) $this->cs->csid, 0, 31);
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['No. Canvas Sheet', $this->cs->csid];
        $rows[] = ['No. SPPB/J/K/T', $this->cs->sppbjktid];

        $this->headerRow = count($rows) + 1;
        $rows[] = $this->buildHeadingRow();

        foreach ($this->detail as $item) {
            $rows[] = $this->buildDataRow($item);
        }

        $this->lastDataRow = count($rows);
        $rows[] = [];

        $this->summaryStartRow = count($rows) + 1;
        foreach (['Total', 'PPN', 'Grand', 'Selected'] as $label) {
            $rows[] = $this->buildSummaryRow($label);
        }

        return $rows;
    }

    private function buildHeadingRow(): array
    {
        $row = self::FIXED_HEADINGS;

        foreach ($this->vendors as $v) {
            $row[] = $v['vendorname'];
            $row[] = 'Payment Term: '.($v['top_name'] ?: '-');
        }

        return $row;
    }

    private function buildDataRow($item): array
    {
        $row = [
            $item->inventoryid,
            $item->inventory_descr,
            $item->csnote_detail,
            (float) $item->qty,
            $item->uom,
            $item->budget_department_fin_id,
            $item->budget_account_id,
            $item->budget_business_unit_id,
            $item->budget_department_fin_id,
            $item->account_descr,
            (float) $item->inventory_last_price,
        ];

        foreach ($this->vendors as $v) {
            $i = $v['i'];
            $row[] = (float) ($item->{"vendorprice{$i}"} ?? 0);
            $row[] = (float) ($item->{"vendortotalprice{$i}"} ?? 0);
        }

        return $row;
    }

    private function buildSummaryRow(string $label): array
    {
        $row = array_fill(0, count(self::FIXED_HEADINGS), '');

        foreach ($this->vendors as $v) {
            $total = (float) ($v['total'] ?? 0);
            $grand = (float) ($v['grand'] ?? 0);

            $row[] = $label.':';
            $row[] = match ($label) {
                'Total' => $total,
                'PPN' => $grand - $total,
                'Grand' => $grand,
                'Selected' => (float) ($v['selected_grand'] ?? 0),
                default => 0,
            };
        }

        return $row;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalCols = count(self::FIXED_HEADINGS) + count($this->vendors) * 2;
                $lastCol = Coordinate::stringFromColumnIndex(max($totalCols, 2));
                $fixedLastCol = Coordinate::stringFromColumnIndex(count(self::FIXED_HEADINGS));

                // Info rows
                $sheet->getStyle('A1:A2')->getFont()->setBold(true);

                if ($this->docUrl) {
                    $sheet->getCell('B2')->getHyperlink()->setUrl($this->docUrl);
                    $sheet->getStyle('B2')->getFont()->setUnderline(true)->getColor()->setARGB('FF2563EB');
                }

                // Table header row
                $sheet->getStyle("A{$this->headerRow}:{$lastCol}{$this->headerRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE5E7EB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD1D5DB']]],
                ]);

                // Data rows: borders + number formats
                $firstDataRow = $this->headerRow + 1;
                if ($this->lastDataRow >= $firstDataRow) {
                    $sheet->getStyle("A{$firstDataRow}:{$lastCol}{$this->lastDataRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE5E7EB']]],
                    ]);
                    $sheet->getStyle("D{$firstDataRow}:D{$this->lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("K{$firstDataRow}:{$lastCol}{$this->lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                }

                // Summary block
                if (!empty($this->vendors)) {
                    $summaryEndRow = $this->summaryStartRow + 3;

                    $sheet->mergeCells("A{$this->summaryStartRow}:{$fixedLastCol}{$summaryEndRow}");
                    $sheet->setCellValue("A{$this->summaryStartRow}", 'Summary');
                    $sheet->getStyle("A{$this->summaryStartRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    $vendorFirstCol = count(self::FIXED_HEADINGS) + 1;
                    foreach ($this->vendors as $idx => $v) {
                        $valueCol = Coordinate::stringFromColumnIndex($vendorFirstCol + $idx * 2 + 1);
                        $sheet->getStyle("{$valueCol}{$this->summaryStartRow}:{$valueCol}{$summaryEndRow}")
                            ->getNumberFormat()->setFormatCode('#,##0.00');
                    }

                    $sheet->getStyle("A{$this->summaryStartRow}:{$lastCol}{$summaryEndRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF9FAFB']],
                    ]);

                    $sheet->freezePane("A{$firstDataRow}");
                }
            },
        ];
    }
}
