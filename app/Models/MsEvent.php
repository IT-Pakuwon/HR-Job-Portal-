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
        'event_name',
        'event_company_name',
        'event_type',
        'event_status',
        'location_id',
        'sub_location_id',
        'event_start_date',
        'event_end_date',
        'event_total_area',
        'event_description',
        'product_check_exp',
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

    public function location()
    {
        return $this->belongsTo(MsLocation::class, 'location_id', 'location_id');
    }

    public function subLocation()
    {
        return $this->belongsTo(MsSubLocation::class, 'sub_location_id', 'sub_location_id');
    }
}
