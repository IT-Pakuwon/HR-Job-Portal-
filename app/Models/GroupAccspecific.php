<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupAccspecific extends Model
{
    protected $table = "ms_group_acc_specific";
    
    protected $fillable = [
        'group_access_id',
        'group_access_name',
        'username',
        'department_id',
        'parameter_access_id',    
        'status',
        'created_user',
        'updated_user',
        'completed_user' 
    ];


}

