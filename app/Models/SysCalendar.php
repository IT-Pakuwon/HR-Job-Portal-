<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysCalendar extends Model
{
    protected $connection = 'pgsql2';
    protected $table = 'sys_calendar_exception';

    public $timestamps = false;

    protected $fillable = [
        'date_calendar','perpost_date_calendar','date_calendar_descr','date_calendar_type','internal_date_exception','external_date_exception','status','created_by','created_at','updated_by','updated_at','deleted_by','deleted_at'    ];
}
