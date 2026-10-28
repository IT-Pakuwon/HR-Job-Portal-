<?php

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;

class ReportCsDetailExport
{
    private const HEADINGS = [
        'CS_ID', 'Date', 'Cpny', 'PO', 'SPPBJKT', 'BAST No', 'Purchasing',
        'Department', 'Requester', 'Purpose', 'InventoryID', 'Description',
        'Qty', 'UOM', 'Vendor', 'Unit Price', 'DPP', 'Tax', 'Grand Total',
        'Budget Year', 'Budget Business Unit', 'Budget Department', 'COA',
        'Activity Description', 'Status',
    ];

    public function __construct(private Builder $sourceQuery, private \Closure $mapRow)
    {
    }

    public function query(): Builder
    {
        return $this->sourceQuery;
    }

    public function map($row): array
    {
        $values = ($this->mapRow)($row);

        return array_map(fn ($heading) => $values[$heading] ?? null, self::HEADINGS);
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function writeTo(string $path): void
    {
        $options = new Options();
        $writer = new Writer($options);
        $numberStyle = method_exists(Style::class, 'withFormat')
            ? (new Style())->withFormat('#,##0.00')
            : (new Style())->setFormat('#,##0.00');
        $headerStyle = method_exists(Style::class, 'withFontBold')
            ? (new Style())->withFontBold(true)
            : (new Style())->setFontBold();
        $writer->openToFile($path);

        try {
            $writer->addRow(new Row(array_map(
                fn ($heading) => new StringCell($heading, $headerStyle), $this->headings()
            )));
            $query = clone $this->sourceQuery;
            $query->chunk($this->chunkSize(), function ($rows) use ($writer, $numberStyle) {
                foreach ($rows as $row) {
                    $cells = [];
                    foreach ($this->map($row) as $index => $value) {
                        // Explicit string cells preserve text beginning with '=' and leading zeroes.
                        $cells[] = is_string($value) ? new StringCell($value, null)
                            : Cell::fromValue($value, in_array($index, [12, 15, 16, 17, 18], true) ? $numberStyle : null);
                    }
                    $writer->addRow(new Row($cells));
                }
            });
        } finally {
            $writer->close();
        }
    }
}
