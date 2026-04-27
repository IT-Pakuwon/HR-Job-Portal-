<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TrRfpNonPurch extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_rfp_nonpurchase";

    protected $fillable = [
        'rfpnonpurchaseid', 'imnonpurchaseid', 'rfpnonpurchasedate', 'datediperlukan', 'datepenyelesaian', 'cpny_id', 'department_id', 
        'location_id', 'user_peminta', 'rfpnonpurchase_type', 'groupbiaya_id', 'pleasepayto', 'keperluan', 'imnonpurchase_kepada', 'imnonpurchase_tembusan', 
        'amountrequestpayment', 'status', 'userreceive', 'receivedate', 'statusreceive', 'userpayment', 'paymentdate', 'paymenttype', 'amountpayment', 'amountpenyelesaian', 
        'statuspayment', 'calrid', 'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at', 'completed_by', 'completed_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

   
}