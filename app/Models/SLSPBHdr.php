<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SLSPBHdr extends Model
{
    protected $connection = 'sqlsrv5';
    protected $table = "Staging_SPBHdr";
    // protected $primaryKey = 'id';
    protected $fillable = [
        'CpnyID' , 'Crtd_DateTime' , 'Crtd_Prog' , 'Crtd_User' , 'DeptID' , 'InfoHD'
        ,'IsTransfer' , 'LUpd_DateTime' , 'Lupd_Prog' , 'LUpd_User' , 'Manager'
        ,'Peminta' , 'RefDeptID' , 'SPBDate' , 'SPBID' , 'User01' , 'User02'
        ,'User03' , 'User04' , 'User05' , 'User06' , 'User07' , 'User08'
        ,'WOID' , 'TotalRecord' , 'Process_Flag' , 'Created_DateTime' , 'Process_DateTime'
        ];    
}