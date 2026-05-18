<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsParkingKendaraan extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "ms_parking_kendaraan";

    protected $fillable = [
        'site_id_parking','parking_type','worker_type','nopol','jenis_kendaraan','username','nama','cpny_id','department_id','perpost',
        'startdate','enddate','no_kartu','attach_stnk','attach_idcard','attach_bukti_bayar','status',
        'created_by','created_at','updated_by','updated_at','deleted_by','deleted_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
