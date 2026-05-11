<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrApprovalHistory extends Model
{
    // protected $connection = 'mysql2';
    protected $connection = 'pgsql2';
    protected $table = "tr_approval_history";
    protected $primaryKey = 'id';
    protected $fillable = [
        'refnbr',
        'aprv_leveling',
        'aprv_doctype',
        'aprv_cpnyid',
        'aprv_departementid',
        'aprv_username',
        'aprv_name',
        'aprv_datebefore',
        'aprv_dateafter',
        'aprv_type',
        'aprv_condition',
        'aprv_start_nominal',
        'aprv_end_nominal',
        'aprv_duration',
        'aprv_purpose',
        'status',
        'created_by',
        'updated_by',
    ];
}
