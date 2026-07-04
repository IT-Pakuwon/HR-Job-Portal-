<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class LuckydrawParticipantImport implements ToCollection, WithStartRow
{
    protected Collection $rows;

    public function startRow(): int
    {
        return 2;
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
