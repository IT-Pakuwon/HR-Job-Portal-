<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicantDriverLicense extends Model
{
    protected $table = 'hr_ms_applicant_driver_license';

    public $timestamps = false; // kalau tidak ada created_at

    protected $fillable = [
        'applicant_id',
        'driver_license_id',
        'driver_license_descr',
        'status',
    ];
}
