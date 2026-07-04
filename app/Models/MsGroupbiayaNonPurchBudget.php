<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MsGroupbiayaNonPurchBudget extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_groupbiaya_nonpurch_budget";

    protected $fillable = [
        'budget_cpny_id', 'budget_business_unit_id', 'budget_department_fin_id', 'budget_account_id',
        'groupbiaya_id', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at',
        'deleted_by', 'deleted_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }
   

   
}