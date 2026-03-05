<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArrayExport implements FromCollection, WithHeadings
{
    protected $rows;
    protected $headers;

    public function __construct($rows)
    {
        $this->rows = collect($rows);

        $this->headers = count($rows) ? array_keys($rows->first()) : [];
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
