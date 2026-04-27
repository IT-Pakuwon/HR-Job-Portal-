<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrRfpNonPurchDetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_rfp_nonpurchase_detail";

    protected $fillable = [
        'rfpnonpurchaseid', 'keperluan_detail', 'amount_request', 'amount_request_penyelesaian', 'budget_perpost', 'budget_cpny_id', 'budget_business_unit_id', 
        'budget_department_fin_id', 'budget_account_id', 'budget_activity_id', 'budget_activity_descr', 'refid', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

   
}