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
        'refnbr' , 'prev_refnbr' , 'doctype' , 'submitdate' , 'perpost_year' , 'perpost_month' , 'cpny_id' , 'business_unit_id' , 
        'department_fin_id' , 'account_id' , 'activity_id' , 'activity_descr' , 'activity_type' , 'budget_type' , 
        'trancation_activity' , 'budget_amount' , 'status' , 'created_by' , 'created_at' , 
        'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];

}
