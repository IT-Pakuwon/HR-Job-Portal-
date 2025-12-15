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
        'refnbr' , 'doctype' , 'submitdate' , 'spbid' , 'issueid' , 'sppbjktid' , 'csid' , 'ponbr' , 
        'perpost_year' , 'perpost_month' , 'cpny_id' , 'business_unit_id' , 'department_fin_id' , 
        'account_id' , 'activity_id' , 'activity_descr' , 'budget_flow' , 'transaction_source' , 'budget_direction' , 
        'budget_amount' , 'status' , 'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 
        'deleted_by' , 'deleted_at'
    ];

}
