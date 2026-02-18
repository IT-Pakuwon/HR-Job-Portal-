<?php

namespace App\Imports;

use App\Models\MsBudgetTemp;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Auth;

class MsBudgetTempImport implements ToModel, WithHeadingRow
{
    protected $temp_id;
    protected $cpny_id;
    protected $business_unit_id;
    protected $department_fin_id;

    public function __construct($temp_id,$cpny_id, $business_unit_id,$department_fin_id)
    {
        $this->temp_id = $temp_id;
        $this->cpny_id = $cpny_id;
        $this->business_unit_id = $business_unit_id;
         $this->department_fin_id = $department_fin_id;
    }

    public function model(array $row)
    {
        $total = 0;

        for ($i = 1; $i <= 12; $i++) {
            $period = 'period' . str_pad($i, 2, '0', STR_PAD_LEFT) . '_budget';
            $value = $this->cleanNumber($row[$period] ?? null);
            $total += $value;
            $row[$period] = $value; // overwrite with cleaned value
        }

        return new MsBudgetTemp([
            'temp_budget_id'     => $this->temp_id,
            'cpny_id'            => $this->cpny_id,
            'business_unit_id'   => $this->business_unit_id,
            'department_fin_id'  => $this->department_fin_id,

            'perpost'            => trim($row['perpost'] ?? null),
            'account_id'         => trim($row['account_id'] ?? null),
            'activity_id'        => trim($row['activity_id'] ?? null),
            'activity_descr'     => trim($row['activity_descr'] ?? null),
            'activity_detail'    => trim($row['activity_detail'] ?? null),

            'qty_budget'         => $this->cleanNumber($row['qty_budget'] ?? null),
            'unit_price_budget'  => $this->cleanNumber($row['unit_price_budget'] ?? null),
            'totalbudget'        => $total,

            'period01_budget'    => $row['period01_budget'],
            'period02_budget'    => $row['period02_budget'],
            'period03_budget'    => $row['period03_budget'],
            'period04_budget'    => $row['period04_budget'],
            'period05_budget'    => $row['period05_budget'],
            'period06_budget'    => $row['period06_budget'],
            'period07_budget'    => $row['period07_budget'],
            'period08_budget'    => $row['period08_budget'],
            'period09_budget'    => $row['period09_budget'],
            'period10_budget'    => $row['period10_budget'],
            'period11_budget'    => $row['period11_budget'],
            'period12_budget'    => $row['period12_budget'],

            'status'             => 'P',
            'created_by'         => Auth::user()->username ?? 'system',
            'updated_by'         => Auth::user()->username ?? 'system',
        ]);
    }

    private function cleanNumber($value)
    {
        if (is_null($value)) return 0;

        $value = trim((string) $value);

        if ($value === '' || $value === '-') return 0;

        // Remove commas
        $value = str_replace(',', '', $value);

        return is_numeric($value) ? (float) $value : 0;
    }


}



