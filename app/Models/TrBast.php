<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrBast extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_bast";

    protected $fillable = [
        'bastid',
        'bastdate',
        'ponbr',
        'cpny_id',
        'csid',
        'sppbjktid',
        'bqid',
        'department_id',
        'user_peminta',
        'keperluan',
        'order_term',
        'terms_id',
        'topid',
        'progress_pct',
        'payment_pct',
        'vendorid',
        'vendorname',
        'startdate',
        'enddate',
        'handoverdate',
        'bast_amount',
        'days_penalty',
        'penalty',
        'total_penalty',
        'realize_amount',
        'rating_vendor',
        'spkpic',
        'spkwarranty',
        'status',
        'created_by',
        'updated_by',
        'completed_by'

    ];
 

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function userpeminta()
    {
        return $this->belongsTo(User::class, 'user_peminta', 'username');
    }

    
}
