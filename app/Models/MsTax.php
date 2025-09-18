<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsTax extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_tax";

    protected $fillable = [
        'taxid',
        'taxrate',
        'descr',
        'taxtype',
        'status',
        'created_by',
        'updated_by',
    ];
}
