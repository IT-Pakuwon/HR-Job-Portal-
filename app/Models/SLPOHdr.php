<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SLPOHdr extends Model
{
    protected $connection = 'sqlsrv5';
    protected $table = "Staging_POHdr";
    public $timestamps = false;                 // ✅ PENTING (hilangkan created_at/updated_at)
    // protected $primaryKey = 'id';
    protected $fillable = [
        'CpnyID','Crtd_DateTime','Crtd_Prog','Crtd_User','CSID','CSDate','DeptID','IsTransfer',
        'IsCancel','JenisPekerjaan','LocationID','Lupd_DateTime','Lupd_Prog','LUpd_User',
        'Manager','MaterialService','NamaPeminta','Note','Purchaser','SPPBNbr','SPPBDate',
        'User01','User02','User03','User04','User05','User06','User07','User08',
        'TotalRecord','Process_Flag','Created_DateTime','Process_DateTime','Process_Note'
        ];    
}