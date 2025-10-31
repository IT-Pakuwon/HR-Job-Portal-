<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrBudget extends Model
{
    use HasFactory;


    protected $connection = 'pgsql';
    protected $table = 'tr_budget';   
    protected $fillable = [     
        'budget_id', 'budget_date', 'perpost', 'cpny_id', 'business_unit_id', 'department_fin_id', 'totalbudget', 
        'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at', 'completed_by',
        'completed_at'
    ];
   
}
