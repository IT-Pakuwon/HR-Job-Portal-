<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrRfpNonPurchDeposit extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_rfp_nonpurchase_deposit";

    protected $fillable = [
        'rfpnonpurchaseid', 'cpny_id', 'custid', 'customername', 'storename', 'unitid', 'transferto', 'bankname', 'bankacct', 'status', 
        'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

   
}