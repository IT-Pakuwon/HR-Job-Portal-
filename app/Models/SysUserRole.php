<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysUserRole extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "sys_user_role";   
    public $timestamps = false;

    protected $fillable = [
        'username',
        'role_id',
        'status',       
        'created_by',
        'updated_by'
       
    ];

    
}
