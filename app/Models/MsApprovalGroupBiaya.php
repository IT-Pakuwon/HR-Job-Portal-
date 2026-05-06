<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsApprovalGroupBiaya extends Model
{
    // protected $connection = 'mysql2';
    protected $connection = 'pgsql2';
    protected $table = "ms_approval_groupbiaya";
    protected $primaryKey = 'id';
    protected $fillable = [
       'aprv_leveling', 'aprv_doctype', 'aprv_cpnyid', 'aprv_departementid', 'aprv_username', 'aprv_name', 'aprv_groupbiaya', 'aprv_typecondition', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];
}
