<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrParkingRegistrationDetail extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "tr_parking_registration_detail";

    protected $fillable = [
        'docid','parking_type','worker_type','nopol','jenis_kendaraan','username','nama','cpny_id','department_id','site_id_parking','perpost',
        'startdate','enddate','nopol_lama','jenis_lama','ref_nbr','attach_stnk','attach_idcard','attach_bukti_bayar','status',
        'created_by','created_at','updated_by','updated_at','deleted_by','deleted_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
