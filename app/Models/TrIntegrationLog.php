<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrIntegrationLog extends Model
{

    protected $connection = 'pgsql2';
    protected $table = "tr_integration_log";
    protected $primaryKey = 'id';
    protected $fillable = [
        'integration_id' ,'setting_id' , 'setting_name' , 'refnbr' , 
        'payload' , 'payload_response' , 'payload_status' , 'payload_message' , 'status' , 
        'created_by' , 'created_at' , 'updated_by' , 'updated_at' , 'deleted_by' , 'deleted_at'
    ];
}