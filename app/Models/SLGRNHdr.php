<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SLGRNHdr extends Model
{
    protected $connection = 'sqlsrv5';
    protected $table = "Staging_GRNHdr";
    public $timestamps = false;                 // ✅ PENTING (hilangkan created_at/updated_at)
    // protected $primaryKey = 'id';
    protected $fillable = [
        'AcumCrtdBy','AcumCrtdOn','CpnyID','Crtd_DateTime','Crtd_Prog','CurryID'
        ,'IsValidPO','LUpd_DateTime','LUpd_Prog','PONbr','PostPeriod','ReceiptDate'
        ,'ReceiptNbr','ReceiptType','Requestor','SLBatNbr','SPPB','StatusHdr'
        ,'Tot_Amount','Tot_Qty','User01','User02','User03','User04','User05'
        ,'User06','User07','User08','User09','VendID','VendName','TotalRecord'
        ,'Process_Flag','Created_DateTime','Process_DateTime'
        ];    
}