<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsPurchSetting extends Model
{

    protected $connection = 'pgsql';
    protected $table = "ms_purch_setting";
    protected $primaryKey = 'id';
    protected $fillable = [
        'setting_id',
        'setting_name',
        'setting_value_string',
        'setting_value_int',
        'status',
        'created_by',
        'updated_by',
    ];
}
