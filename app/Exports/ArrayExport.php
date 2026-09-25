<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ArrayExport implements FromCollection, WithHeadings, WithColumnFormatting
{
    protected $rows;
    protected $headers;
    protected $formats;

    /**
     * @param array $formats Excel number formats keyed by header name,
     *                       e.g. ['Price' => '"Rp" #,##0.00']
     */
    public function __construct($rows, array $formats = [])
    {
        $this->rows = collect($rows);

        $first = $this->rows->first();

        $this->headers = $first ? array_keys($first) : [];

        $this->formats = $formats;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function columnFormats(): array
    {
        $result = [];

        foreach ($this->formats as $header => $format) {
            $index = array_search($header, $this->headers, true);
            if ($index !== false) {
                $result[Coordinate::stringFromColumnIndex($index + 1)] = $format;
            }
        }

        return $result;
    }
}
