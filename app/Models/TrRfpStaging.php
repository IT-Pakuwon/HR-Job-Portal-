<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrRfpStaging extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_rfp_staging";

    protected $fillable = [   
       'irid', 'irdate', 'irsubmitdate', 'cpny_id', 'vendor_id', 'vendor_name', 'ponbr', 'kontrakid', 'csid', 'sppbjktid', 'bastid', 'departementid', 
       'keperluan', 'pobaseamount', 'potaxamount', 'poamount', 'rfpid', 'typepo', 'typepaymentinvreg', 'periodpayment', 'rfpbaseamount', 'rfptaxamount', 
       'rfpamount', 'irnote', 'status', 'created_user', 'created_at', 'updated_user', 'updated_at', 'deleted_by', 'deleted_at', 'completed_by', 'completed_at'
    ];
    

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }  

    
}
