<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;

class CarExpenseImport implements WithMultipleSheets
{
    protected CarExpenseTemplateSheetImport $sheetImport;

    public function __construct()
    {
        $this->sheetImport = new CarExpenseTemplateSheetImport();
    }

    public function sheets(): array
    {
        // Only read the first sheet (index 0 = Template), ignore Instructions sheet
        return [
            0 => $this->sheetImport,
        ];
    }

    public function getRows(): Collection
    {
        return $this->sheetImport->getRows();
    }
}

class CarExpenseTemplateSheetImport implements ToCollection, WithStartRow
{
    protected Collection $rows;

    public function startRow(): int
    {
        return 2; // Row 1 is the header
    }

    public function collection(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function getRows(): Collection
    {
        return $this->rows ?? collect();
    }
}
