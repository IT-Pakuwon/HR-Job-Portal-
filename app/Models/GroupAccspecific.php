<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupAccspecific extends Model
{
    protected $connection = 'pgsql3';
    protected $table = "ms_group_acc_specific";

    protected $fillable = [
        'group_cpny_id',
        'area_id',
        'group_access_id',
        'group_access_name',
        'username',
        'department_id',
        'parameter_access_id',
        'status',
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',
    ];
}

