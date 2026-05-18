<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrParkingRegistration extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "tr_parking_registration";

    protected $fillable = [
        'docid','parking_regist_date','cpny_id','department_id','location_id','user_peminta','site_id_parking','parking_type','worker_type','perpost',
        'info','status','created_by','created_at','updated_by','updated_at','deleted_by','deleted_at','completed_by','completed_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
