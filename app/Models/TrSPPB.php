<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrSPPB extends Model
{
    protected $connection = 'pgsql';
    protected $table = "tr_sppb";

    protected $fillable = [
        'sppbid',
        'sppbdate',
        'cpny_id',
        'department_id',
        'requesttypeid',
        'keperluan',
        'budget_perpost',
        'woid',
        'spbid',
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
    
}
