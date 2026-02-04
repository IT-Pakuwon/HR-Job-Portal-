<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userbusinessunit extends Model
{
    protected $connection = 'pgsql2';
    protected $table = "ms_user_business_unit";
    protected $primaryKey = 'id';
    protected $fillable = [
        'username',
        'cpny_id',
        'business_unit_id',       
        'status',
        'created_by',
        'updated_by',
    ];
}
