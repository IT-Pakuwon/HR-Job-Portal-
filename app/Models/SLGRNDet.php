<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SLGRNDet extends Model
{
    protected $connection = 'sqlsrv5';
    protected $table = "Staging_GRNDet";
    public $timestamps = false;                 // ✅ PENTING (hilangkan created_at/updated_at)
    // protected $primaryKey = 'id';
    protected $fillable = [
        'AcumCrtdBy','AcumCrtdOn','AcumRowID','CpnyID','Crtd_DateTime','Crtd_Prog'
        ,'DiscAmt','DiscPct','ExtCost','InvtDescr','InvtID','InvtID_SL','LineID'
        ,'LineType','LUpd_DateTime','LUpd_Prog','POLineRef','PONbr','QtyRcpt'
        ,'ReceiptNbr','RcptNbrToRet','SiteID','SL_POLineID','SL_POLineRef'
        ,'TaxAmt','TaxID','UnitPrice','UOM','User01','User02','User03'
        ,'User04','User05','User06','User07','User08','User09','WhseLoc'
        ];
}