<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsEvent extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_event';

    protected $primaryKey = 'id';

    protected $fillable = [
        'event_id',
        'event_create_date',
        'cpnyid',
        'department_id',
        'event_name',
        'event_company_name',
        'event_type',
        'event_status',
        'event_location_id',
        'event_start_date',
        'event_end_date',
        'event_description',
        'event_total_contract',
        'pic_event',
        'pic_event_external',
        'pic_event_external_hp',
        'status',
        'created_user',
        'updated_user',
        'deleted_by',
    ];

    protected $casts = [
        'event_create_date' => 'date:Y-m-d',
        'event_start_date' => 'date:Y-m-d',
        'event_end_date' => 'date:Y-m-d',
    ];

    public function company()
    {
        return $this->belongsTo(MsCompany::class, 'cpnyid', 'cpny_id');
    }

    public function department()
    {
        return $this->belongsTo(MsDepartment::class, 'department_id', 'department_id');
    }
}
