<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArrayExport implements FromCollection, WithHeadings
{
    protected $rows;
    protected $headers;

    public function __construct($rows)
    {
        $this->rows = collect($rows);

        $first = $this->rows->first();

        $this->headers = $first ? array_keys($first) : [];
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headers;
    }
}
