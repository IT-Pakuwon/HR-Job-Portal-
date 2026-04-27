<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrRfpNonPurchSettlement extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_rfp_nonpurchase_settlement";

    protected $fillable = [
        'calrnonpurchaseid', 'rfpnonpurchaseid', 'calrnonpurchasedate', 'datebataspenyelesaian', 'cpny_id', 'department_id', 'location_id', 
        'user_peminta', 'keperluan', 'amountrfp', 'amountsettlement', 'amountdiff', 'status', 'userreceive', 'receivedate', 'statusreceive', 'userpayment', 
        'paymentdate', 'paymenttype', 'amountpayment', 'amountpenyelesaian', 'statuspayment', 'created_by', 'created_at', 
        'updated_by', 'updated_at', 'deleted_by', 'deleted_at', 'completed_by', 'completed_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

   
}