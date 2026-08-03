<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignPayroll extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "ms_approval_payroll";
    // protected $primaryKey = 'id';
    protected $fillable = [
        'docid',
        'cpnyid',
        'group_cpny_id',
        'aprvid',
        'jabatan',
        'aprvusername',
        'name',
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at'
    ];

  


}
