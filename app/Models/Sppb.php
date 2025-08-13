<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sppb extends Model
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
        'created_by'
           
    ];

   

}
