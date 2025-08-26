<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_budget_hd";
    
    protected $fillable = [
        'budget_id',
        'budget_date',
        'perpost',
        'cpny_id',
        'business_unit_id',
        'department_fin_id',        
        'totalbudget',        
        'status',
        'created_by',
        'updated_by',
        'completed_by',

    ];

   

}
