<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsDasSetting extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "ms_das_setting";
    
    protected $fillable = [
        'setting_id', 'setting_name', 'setting_value_string', 'setting_value_int', 'setting_value_datetime', 'status', 
        'created_by', 'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
