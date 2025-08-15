<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SignPayroll extends Model
{
    protected $table = "ms_approval_payroll";
    // protected $primaryKey = 'id';
    protected $fillable = [
        'aprvid',
        'docid',
        'jabatan',
        'aprvusername',
        'name',
        'status',
        'created_user',
        'updated_user'
    ];

  


}
