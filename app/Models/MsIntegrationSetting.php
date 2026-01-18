<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsIntegrationSetting extends Model
{

    protected $connection = 'pgsql2';
    protected $table = "ms_integration_setting";
    protected $primaryKey = 'id';
    protected $fillable = [
        'integration_id' ,'setting_id' , 'setting_name' , 'setting_value_string' , 'setting_value_int' , 'status' , 
        'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];
}
