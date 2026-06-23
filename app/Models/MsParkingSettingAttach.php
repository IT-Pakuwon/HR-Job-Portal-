<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsParkingSettingAttach extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "ms_parking_setting_att";

    protected $fillable = [
        'parking_type', 'worker_type', 'att_stnk', 'att_idcard', 'att_buktibayar', 'status', 'created_by', 
        'created_at', 'updated_by', 'updated_at', 'deleted_by', 'deleted_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
