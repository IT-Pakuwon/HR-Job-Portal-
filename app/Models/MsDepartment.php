<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsDepartment extends Model
{
    // protected $connection = 'mysql2';
    protected $connection = 'pgsql2';
    protected $table = "ms_department";
    protected $primaryKey = 'id';
    protected $fillable = [
        'cpny_id',
        'department_id',
        'department_name',
        'department_fin_id',
        'status'
    ];
}
