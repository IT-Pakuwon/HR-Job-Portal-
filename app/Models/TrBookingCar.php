<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrBookingCar extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "tr_booking_car";

    protected $fillable = [
        'docid', 'booking_date', 'cpny_id', 'department_id', 'location_id', 'user_peminta', 'site_id', 'cpny_id_site', 'purpose_id',
        'purpose_descr', 'start_time', 'end_time', 'location_from', 'destination', 'user_request', 'driver', 'handphone', 'no_polisi', 'passenger',
        'checked_by', 'checked_at', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at', 'completed_by', 'completed_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }
}
