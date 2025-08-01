<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeSto extends Model
{
    protected $table = "hr_ms_request_change_sto";   
    protected $fillable = [
        'changerequest_id',
        'changerequest_date',
        'cpnyid',
        'departementid',
        'user',
        'departement_name',
        'subgrade_name',
        'changerequest_note',
        'status',  
        'created_user',
        'created_at',
        'updated_user',
        'updated_at',
        'status',  
        'completed_user',      
           
    ];

   

}
