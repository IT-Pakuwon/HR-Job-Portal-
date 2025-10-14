<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsApproval extends Model
{
    // protected $connection = 'mysql2';
    protected $connection = 'pgsql2';
    protected $table = "ms_approval";
    protected $primaryKey = 'id';
    protected $fillable = [
        'aprv_leveling',
        'aprv_doctype',
        'aprv_cpnyid',
        'aprv_departementid',
        'aprv_username',
        'aprv_name',
        'aprv_type',
        'aprv_condition',
        'aprv_start_nominal',
        'aprv_end_nominal',
        'status',
        'created_by',
        'updated_by',
    ];
}
