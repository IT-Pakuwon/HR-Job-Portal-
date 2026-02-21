<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIfcaMappingDivDept  extends Model
{

    protected $connection = 'pgsql4';
    protected $table = "staging_ifca_mapping_div_dept";
    protected $primaryKey = 'id';
    protected $fillable = [
        'entity_cd' , 'acct_type' , 'acct_cd' , 'div_cd' , 'dept_cd' , 'status' , 
        'created_by' , 'created_at' , 'updated_by' , 'updated_at'
        ];
}
