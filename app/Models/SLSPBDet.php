<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SLSPBDet extends Model
{
    protected $connection = 'sqlsrv5';
    protected $table = "Staging_SPBDet";
    // protected $primaryKey = 'id';
    protected $fillable = [
        'CpnyID' , 'Crtd_DateTime' , 'Crtd_Prog' , 'Crtd_User' , 'DeptID' , 'InfoDT' , 'InvtID'
        ,'IsTransfer' , 'LUpd_DateTime' , 'Lupd_Prog' , 'LUpd_User' , 'Qty' , 'QtyIssued'
        ,'QtyReturn' , 'ReasonCD' , 'RefNbr' , 'SPBAcct' , 'SPBSubAcct' , 'TranDate'
        ,'UnitDes' , 'User01' , 'User02' , 'User03' , 'User04' , 'User05' , 'User06'
        ,'User07' , 'User08'
        ];
}