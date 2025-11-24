<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrCalr extends Model
{
    
    protected $connection = 'pgsql';
    protected $table = 'tr_calr';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [        
        'calrid',
        'calrdate',
        'rfcaid',
        'rfca_type',
        'ponbr',
        'cpny_id',
        'csid',
        'sppbjktid',
        'department_id',
        'user_peminta',
        'keperluan',
        'vendorid',
        'vendorname',
        'rfca_amount',
        'calr_amount',
        'balance_amount',      
        'status',        
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
