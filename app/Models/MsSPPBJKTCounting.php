<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsSPPBJKTCounting extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_sppbjkt_counting";

    protected $fillable = [        
        'doctype',
        'doctype_counting',
        'status'
    ];
}
