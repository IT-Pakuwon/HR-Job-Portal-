<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SysRoleMenu extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "sys_role_menu";   
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'menu_id',
        'parent_menu_id',
        'status',   
        'created_by',
        'updated_by'
       
    ];

    
}
