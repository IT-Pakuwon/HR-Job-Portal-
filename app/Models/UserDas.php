<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDas extends Model
{
    protected $connection = 'mysql2';
    protected $table = "users";   
    protected $fillable = [
        'name',
        'username',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'role',
        'companyid',
        'departmentid',
        'groups',
        'site',
        'status',
        'created_user',
        'updated_user',
        'menu',
        'submenu',
        'test_email',
        'jabatan',
        'position',
        'npk',
        'agama',
        'jenkel',
        'email_bcc',
        'user_id_talenta',
        'job_level',
        'approval_line',
    ];
    
}
