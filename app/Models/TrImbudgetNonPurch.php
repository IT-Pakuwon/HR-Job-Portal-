<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrImbudgetNonPurch extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_imbudget_nonpurchase";

    protected $fillable = [
        'imnonpurchaseid', 'imnonpurchasedate', 'cpny_id', 'department_id', 'location_id', 'user_peminta', 'imnonpurchasetype', 'imbudgetkeperluan', 
        'budget_from', 'budget_to', 'expenditure_type', 'existing_budget', 'request_budget', 'over_budget', 'status', 
        'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at', 'completed_by', 'completed_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

   
}