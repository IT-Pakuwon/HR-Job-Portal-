<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SLPODet extends Model
{
    protected $connection = 'sqlsrv5';
    protected $table = "Staging_PODet";
    public $timestamps = false;                 // ✅ PENTING (hilangkan created_at/updated_at)
    // protected $primaryKey = 'id';
    protected $fillable = [
        'CpnyID','Crtd_DateTime','Crtd_Prog','Crtd_User','CSComplDatetime','CSComplUser'
        ,'CSID','CSLupd_Datetime','CSLupd_User','CuryExtCost','CuryID','CuryUnitCost'
        ,'InvtID','InvtIDDG','InvtTypeCS','IsTransfer','Lupd_DateTime','Lupd_Prog'
        ,'LUpd_User','Note','POLineref','PONbr','PurAcct','PurchaseFor','Purchunit'
        ,'PurSub','QtyOrd','SLCX','TaxID00','TOP_Digital','TranDesc','TypeSPPBJK'
        ,'User01','User02','User03','User04','User05','User06','User07','User08'
        ,'VendorID','VendNoteSelected'
        ];
}