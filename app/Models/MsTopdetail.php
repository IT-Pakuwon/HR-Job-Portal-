<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsTopdetail extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'ms_top_detail';

    protected $fillable = [
        'terms_id',
        'topid',
        'top_type',
        'terms_name',
        'payment_pct',
        'progress_pct',
        'terms_type',
        'flag_bast',
        'status',
        'created_by',        
        'updated_by'
    ];

    
}
