<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsBudgetTemp extends Model
{
    use HasFactory;


    protected $connection = 'pgsql';
    protected $table = 'ms_budget_temp';   
    protected $fillable = [     
        'temp_id',
        'perpost',
        'cpny_id',
        'business_unit_id',
        'department_fin_id',
        'account_id',
        'activity_id',
        'activity_detail',
        'totalbudget',
        'period01_budget',
        'period02_budget',
        'period03_budget',
        'period04_budget',
        'period05_budget',
        'period06_budget',
        'period07_budget',
        'period08_budget',
        'period09_budget',
        'period10_budget',
        'period11_budget',
        'period12_budget',   
        'status',
        'created_by',
        'updated_by'
    ];
   
}
