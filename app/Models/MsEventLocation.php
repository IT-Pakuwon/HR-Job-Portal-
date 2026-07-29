<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsEventLocation extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'ms_event_location';

    protected $fillable = [
        'cpny_id',
        'event_location_id',
        'event_location_name',
        'event_department_id',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function company()
    {
        return $this->belongsTo(MsCompany::class, 'cpny_id', 'cpny_id');
    }

    public function department()
    {
        return $this->belongsTo(MsDepartment::class, 'event_department_id', 'department_id');
    }
}
