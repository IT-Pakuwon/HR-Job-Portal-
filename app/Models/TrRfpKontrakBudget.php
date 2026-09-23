<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrRfpKontrakBudget extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_rfp_kontrak_budget";

    protected $fillable = [
        'rfp_id', 'cpny_id', 'budget_perpost', 'budget_cpny_id', 'budget_business_unit_id', 'budget_department_fin_id', 
        'budget_account_id', 'budget_activity_id', 'budget_activity_descr', 'rfp_base_amount', 'status', 'created_by', 
        'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at', 'completed_by', 'completed_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

   
}