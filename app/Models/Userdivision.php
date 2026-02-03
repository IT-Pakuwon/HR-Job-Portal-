<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userdivision extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "ms_user_division_test";
    protected $primaryKey = 'id';
    protected $fillable = [
        'username',
        'division_id',       
        'status',
        'created_by',
        'updated_by',
    ];
}
