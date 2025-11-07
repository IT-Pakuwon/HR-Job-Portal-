<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class TrPOterm extends Model
{
    // use SoftDeletes;

    protected $connection = 'pgsql';
    protected $table = 'tr_po_term';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        
    ];

    
}
