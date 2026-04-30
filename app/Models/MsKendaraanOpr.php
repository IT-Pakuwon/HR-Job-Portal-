<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsKendaraanOpr extends Model
{
    use HasFactory;

    protected $connection = 'pgsql5';

    protected $table = 'ms_kendaraan_opr';

    protected $fillable = [
        'nopol_kendaraan',
        'kendaraan_descr',
        'status',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
    ];
}
