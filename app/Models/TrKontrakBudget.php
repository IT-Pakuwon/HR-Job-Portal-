<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrKontrakBudget extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_kontrak_budget";

    protected $fillable = [      
        'kontrakid', 'cpny_id', 'csid', 'sppbjktid', 'department_id', 'budget_perpost', 'budget_cpny_id', 
        'budget_business_unit_id', 'budget_department_fin_id', 'budget_account_id', 'budget_activity_id', 
        'budget_activity_descr', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ]; 

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }   
    
}
