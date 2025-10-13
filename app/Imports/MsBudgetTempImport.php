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
            $total += isset($row[$period]) ? floatval($row[$period]) : 0;
        }

        return new MsBudgetTemp([
            'temp_budget_id'     => $this->temp_id,
            'cpny_id'            => $this->cpny_id,
            'business_unit_id'  => $this->business_unit_id, 
            'department_fin_id'  => $this->department_fin_id,                       
            'perpost'            => $row['perpost'] ?? null,
            'account_id'         => $row['account_id'] ?? null,
            'activity_id'        => $row['activity_id'] ?? null,
            'activity_descr'     => $row['activity_descr'] ?? null,
            'activity_detail'    => $row['activity_detail'] ?? null,
            'qty_budget'         => $row['qty_budget'] ?? null,
            'unit_price_budget'  => $row['unit_price_budget'] ?? null,
            'totalbudget'        => $total,
            'period01_budget'    => $row['period01_budget'] ?? null,
            'period02_budget'    => $row['period02_budget'] ?? null,
            'period03_budget'    => $row['period03_budget'] ?? null,
            'period04_budget'    => $row['period04_budget'] ?? null,
            'period05_budget'    => $row['period05_budget'] ?? null,
            'period06_budget'    => $row['period06_budget'] ?? null,
            'period07_budget'    => $row['period07_budget'] ?? null,
            'period08_budget'    => $row['period08_budget'] ?? null,
            'period09_budget'    => $row['period09_budget'] ?? null,
            'period10_budget'    => $row['period10_budget'] ?? null,
            'period11_budget'    => $row['period11_budget'] ?? null,
            'period12_budget'    => $row['period12_budget'] ?? null,
            'status'             => 'P',
            'created_by'         => Auth::user()->username ?? 'system',
            'updated_by'         => Auth::user()->username ?? 'system',
        ]);
    }
}



