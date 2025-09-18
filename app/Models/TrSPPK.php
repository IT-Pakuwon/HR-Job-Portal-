<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrSPPK extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_sppk";

    protected $fillable = [
        'sppkid',
        'sppkdate',
        'cpny_id',
        'department_id',
        'requesttypeid',
        'no_polisi',
        'namakendaraan',
        'pemilikkendaraan',
        'km_kendaraan',
        'keperluan',
        'budget_perpost',      
        'totalopenordered',
        'totalqty',
        'assignby',
        'assigndate',
        'assignpurchasing',
        'csjobs',
        'cs',
        'status',
        'created_by',
        'updated_by',
        'completed_by'
    ];

    public function requestType()
    {
        return $this->belongsTo(MsRequestType::class, 'requesttypeid', 'requesttypeid');           
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'username');
    }

    public function purchaser()
    {
        return $this->belongsTo(User::class, 'assignpurchasing', 'username');
    }

    
}
