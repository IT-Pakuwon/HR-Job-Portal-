<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentUserEng extends Model
{
    protected $connection = 'mysql4';
    protected $table = "department_user";

    protected $fillable = [
        'user_id',
        'department_id',
        'company_id',
        'Last_update_By',

    ];

   
}
