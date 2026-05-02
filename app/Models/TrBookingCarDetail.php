<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrBookingCarDetail extends Model
{
    protected $connection = 'pgsql5';

    protected $table = 'tr_booking_car_detail';

    public $timestamps = false;

    protected $fillable = [
        'docid',
        'cpny_id',
        'origin',
        'destination',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];

    public function header()
    {
        return $this->belongsTo(TrBookingCar::class, 'docid', 'docid');
    }

    public function company()
    {
        return $this->belongsTo(MsCompany::class, 'cpny_id', 'company_code');
    }
}
