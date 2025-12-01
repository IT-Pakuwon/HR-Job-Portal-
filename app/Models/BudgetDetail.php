<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetDetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_budget";
    
    protected $fillable = [       
        'budget_id', 'perpost', 'cpny_id', 'business_unit_id', 'department_fin_id', 'account_id', 'activity_id', 
        'activity_descr', 'activity_detail', 'activity_type', 'qty_budget', 'unit_price_budget', 'totalbudget', 
        'period01_budget', 'period02_budget', 'period03_budget', 'period04_budget', 'period05_budget', 
        'period06_budget', 'period07_budget', 'period08_budget', 'period09_budget', 'period10_budget', 
        'period11_budget', 'period12_budget', 'totalbudget_add', 'period01_budget_add', 'period02_budget_add', 
        'period03_budget_add', 'period04_budget_add', 'period05_budget_add', 'period06_budget_add', 
        'period07_budget_add', 'period08_budget_add', 'period09_budget_add', 'period10_budget_add', 
        'period11_budget_add', 'period12_budget_add', 'total_reserve', 'period01_reserve', 'period02_reserve', 
        'period03_reserve', 'period04_reserve', 'period05_reserve', 'period06_reserve', 'period07_reserve', 
        'period08_reserve', 'period09_reserve', 'period10_reserve', 'period11_reserve', 'period12_reserve', 
        'total_used', 'period01_used', 'period02_used', 'period03_used', 'period04_used', 'period05_used', 
        'period06_used', 'period07_used', 'period08_used', 'period09_used', 'period10_used', 'period11_used', 
        'period12_used', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];

   

}
