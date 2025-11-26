<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userdept extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "ms_user_dept";
    protected $primaryKey = 'id';
    protected $fillable = [
        'username',
        'department_id',       
        'status',
        'created_by',
        'updated_by',
    ];
}
