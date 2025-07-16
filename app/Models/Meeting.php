<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $connection = 'mysql2';
    protected $table = "trx_meeting";

    protected $fillable = [
        'docid',
        'cpnyid',
        'deptname',
        'locationname',
        'date',
        'user',      
        'start',
        'end',  
        'title',
        'descr',
        'participant',
        'participantlist',
        'acc_id',
        'room_id',  
        'checked',       
        'status',
        'created_user',
        'updated_user',
        'site',
        'cpnyid_site',
        'checkin',
        'checkout',
        'fullbooked',
        'info_zoom',
        'zoom_id',
    ];
}
