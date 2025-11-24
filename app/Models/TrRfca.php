<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrRfca extends Model
{
    
    protected $connection = 'pgsql';
    protected $table = 'tr_rfca';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [        
        'rfcaid',
        'rfcadate',
        'ponbr',
        'cpny_id',
        'csid',
        'sppbjktid',
        'department_id',
        'user_peminta',
        'keperluan',
        'order_term',
        'terms_id',
        'topid',
        'payment_pct',
        'vendorid',
        'vendorname',
        'po_amount',
        'rfca_amount',
        'prev_rfcaid',
        'prev_ponbr',
        'prev_csid',
        'prev_rfca_amount',
        'add_rfca_amount',
        'required_date',
        'calr_date',
        'status',
        'rfca_type',
        'rfca_step_order',
        'rfca_step_id',
        'status_rfca',
        'calrid',
        'created_by',
        'updated_by',        
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
