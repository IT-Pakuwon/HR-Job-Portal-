<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usercpny extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "ms_user_cpny_test";
    protected $primaryKey = 'id';
    protected $fillable = [
        'username',
        'cpny_id',
        'status',
        'created_by',
        'updated_by',
    ];
}
