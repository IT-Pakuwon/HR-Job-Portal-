<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrVoucherTaxi extends Model
{
    protected $connection = 'pgsql5';
    protected $table = "tr_voucher_taxi";
    
    protected $fillable = [
        'docid', 'vaucher_date', 'cpny_id', 'department_id', 'location_id', 'user_peminta', 'site_id', 'cpny_id_site', 'to', 'perpose', 'date_used', 
        'cpny_id_expense', 'type_trip', 'max_trip', 'status_trip', 'max_budget', 'actual_budget', 'type_trip_done', 'user_topup', 'cpny_id_budget', 
        'checked_by', 'checked_at', 'status', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

}
