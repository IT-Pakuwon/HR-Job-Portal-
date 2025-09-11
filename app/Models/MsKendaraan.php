<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsKendaraan extends Model
{
    protected $connection = 'pgsql';
    protected $table = "ms_kendaraan";

    protected $fillable = [
        'cpny_id',
        'no_polisi',
        'namakendaraan',
        'typekendaraan',
        'merk_kendaraan',
        'pemilikkendaraan',
        'status',
        'created_by',
        'updated_by'
    ];
}
